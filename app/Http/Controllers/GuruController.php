<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Admin;
use Illuminate\Http\Request;
use Carbon\Carbon;


class GuruController extends Controller
{
    public $timestamps = false;

    // Menampilkan semua data guru
    public function index()
    {
        $guru = Guru::all();
        return response()->json([
            'data' => $guru,
            'message' => 'Success get guru data',
            'code' => 200,
        ]);
    }

    // Menyimpan data guru baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nip' => 'required|integer',
            'username' => 'required|string|max:255|unique:guru,username',
            'password' => 'required|string|min:6',
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
            'message' => 'Data Siswa Berhasil Ditampilkan',
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
        'nip' => 'required|integer', // Tidak perlu unique karena hanya sebagai nomor pegawai
        'username' => 'required|string|max:255|unique:guru,username,' . $id,
        'password' => 'nullable|string|min:6', // Password bisa dikosongkan jika tidak ingin diubah
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

    // Debugging untuk melihat data yang diterima
    // dd($request->all());

    // Hash password jika diisi
    // if ($request->filled('password')) {
    //     $validated['password'] = Hash::make($request->password);
    // } else {
    //     unset($validated['password']);
    // }

    $guru->update($validated);

    return response()->json([
        'data' => $guru,
        'message' => 'Data Guru Berhasil Diperbarui',
        'code' => 200,
    ]);
    }

    // Menghapus data guru
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
