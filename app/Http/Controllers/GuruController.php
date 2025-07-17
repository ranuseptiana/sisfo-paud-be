<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Admin;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GuruController extends Controller
{
    public function exportGuru(Request $request)
    {
        try {
            $allowedColumns = [
                'nip', 'nama_lengkap', 'gender', 'tempat_lahir', 'tgl_lahir',
                'agama', 'alamat', 'no_telp', 'jabatan',
                'jumlah_hari_mengajar', 'tugas_mengajar'
            ];

            $data = DB::table('guru')
                ->select($allowedColumns)
                ->get()
                ->map(function ($item) {
                    $itemArray = (array)$item;

                    // Jika ada field tanggal lahir, ubah ke format string agar tidak error saat parsing JSON
                    if (isset($itemArray['tgl_lahir'])) {
                        $itemArray['tgl_lahir'] = (string)$itemArray['tgl_lahir'];
                    }

                    return $itemArray;
                });

                return response()->json([
                    'success' => true,
                    'data' => $data,
                    'message' => 'Data guru berhasil diekspor'
                ], 200, [
                    'Content-Type' => 'application/json'
                ]);

        } catch (\Exception $e) {
            \Log::error('Export guru error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat ekspor: ' . $e->getMessage()
            ], 500);
        }
    }

    public $timestamps = false;

    public function index()
    {
        $guru = Guru::all();
        return response()->json([
            'data' => $guru,
            'message' => 'Success get guru data',
            'code' => 200,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nip' => 'required|integer',
            'nama_lengkap' => 'required|string|max:255',
            'gender' => 'required|string|max:255',
            'tempat_lahir' => 'required|string|max:255',
            'tgl_lahir' => 'nullable|date',
            'agama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'no_telp' => 'nullable|string|max:15',
            'jabatan' => 'required|string',
            'jumlah_hari_mengajar' => 'required|integer',
            'tugas_mengajar' => 'required|string',
        ]);

        $guru = Guru::create($validated);

        return response()->json([
            'data' => $guru,
            'message' => 'Guru successfully created',
            'code' => 201,
        ], 201);
    }

     /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $guru = Guru::findOrFail($id);

        return response()->json([
            'data' => $guru,
            'message' => 'Data Guru Berhasil Ditampilkan',
            'code' => 200,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {

    $validated = $request->validate([
        'nip' => 'required|integer',
        'nama_lengkap' => 'required|string|max:255',
        'gender' => 'required|string|max:255',
        'tempat_lahir' => 'required|string|max:255',
        'tgl_lahir' => 'nullable|date',
        'agama' => 'required|string|max:255',
        'alamat' => 'nullable|string',
        'no_telp' => 'nullable|string|max:15',
        'jabatan' => 'required|string',
        'jumlah_hari_mengajar' => 'required|integer',
        'tugas_mengajar' => 'required|string',
    ]);

    $guru = Guru::findOrFail($id);

    $guru->update($validated);

    return response()->json([
        'data' => $guru,
        'message' => 'Data Guru Berhasil Diperbarui',
        'code' => 200,
    ]);
    }

    public function destroy($id)
    {
        $guru = Guru::where('id', $id)->first();

        if (!$guru) {
            return response()->json([
                'message' => 'Guru not found',
                'code' => 404,
            ], 404);
        }

        $guru->delete();

        return response()->json([
            'message' => 'Guru successfully deleted',
            'code' => 200,
        ]);
    }
}
