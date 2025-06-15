<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\Cicilan;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PembayaranController extends Controller
{
    /**
     * Display the specified payment with installments for student.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showByPembayaranIdSiswa($id)
    {
        $pembayaran = Pembayaran::with(['siswa', 'cicilan' => function ($query) {
            $query->orderBy('tanggal_cicilan', 'asc');
        }])->where('siswa_id', $id)->get();

        $formattedData = $pembayaran->map(function ($item) {
            $item->append(['total_cicilan', 'sisa_pembayaran', 'status_cicilan']);

            $buktiPembayaranUrl = null;
            if ($item->bukti_pembayaran) {
                $buktiPembayaranUrl = Storage::url($item->bukti_pembayaran);
            }

            return [
                'id_pembayaran' => $item->id,
                'siswa_id' => $item->siswa_id,
                'tanggal_pembayaran' => $item->tanggal_pembayaran,
                'status_pembayaran' => $item->status_pembayaran,
                'status_rapor' => $item->status_rapor,
                'nominal' => $item->nominal,
                'metode_pembayaran' => $item->metode_pembayaran,
                'jenis_pembayaran' => $item->jenis_pembayaran,
                'status_atribut' => $item->status_atribut,
                'bukti_pembayaran' => $item->bukti_pembayaran,
                'bukti_pembayaran_url' => $buktiPembayaranUrl,
                'status_cicilan' => $item->status_cicilan,
                'total_cicilan' => $item->total_cicilan,
                'sisa_pembayaran' => $item->sisa_pembayaran,
                'cicilan' => $item->cicilan,
                'siswa' => $item->siswa
            ];
        });

        return response()->json($formattedData);
    }

    public function showByJenisPembayaran($idSiswa, $jenis)
    {
        $user = auth()->user();
        if ($user->id != $idSiswa) {
            return response()->json([
                'message' => 'Anda hanya bisa mengakses data pembayaran sendiri',
                'code' => 403
            ], 403);
        }

        $jenis = strtolower(str_replace(' ', '_', $jenis));

        $validJenis = ['pendaftaran_baru', 'daftar_ulang'];
        if (!in_array($jenis, $validJenis)) {
            return response()->json([
                'message' => 'Jenis pembayaran tidak valid. Pilih: pendaftaran_baru atau daftar_ulang',
                'code' => 400
            ], 400);
        }

        $dbJenis = str_replace('_', ' ', $jenis);

        $pembayaran = Pembayaran::with([
                'siswa:id,nisn,nama_siswa,tahun_ajaran_id',
                'siswa.tahunAjaran:id,tahun',
                'cicilan:id,pembayaran_id,nominal_cicilan,tanggal_cicilan,status_verifikasi'
            ])
            ->where('siswa_id', $user->siswa_id)
            ->where('jenis_pembayaran', $dbJenis)
            ->get();

        if ($pembayaran->isEmpty()) {
            return response()->json([
                'message' => 'Data pembayaran tidak ditemukan',
                'code' => 404
            ], 404);
        }

        $formattedData = $pembayaran->map(function ($item) {
            $item->append(['total_cicilan', 'sisa_pembayaran', 'status_cicilan']);

            $buktiPembayaranUrl = null;
            if ($item->bukti_pembayaran) {
                $buktiPembayaranUrl = Storage::url($item->bukti_pembayaran);
            }

            return [
                'id_pembayaran' => $item->id,
                'siswa_id' => $item->siswa_id,
                'tanggal_pembayaran' => $item->tanggal_pembayaran,
                'status_pembayaran' => $item->status_pembayaran,
                'status_rapor' => $item->status_rapor,
                'metode_pembayaran' => $item->metode_pembayaran,
                'nominal' => $item->nominal,
                'jenis_pembayaran' => $item->jenis_pembayaran,
                'status_atribut' => $item->status_atribut,
                'bukti_pembayaran' => $item->bukti_pembayaran,
                'bukti_pembayaran_url' => $buktiPembayaranUrl,
                'status_cicilan' => $item->status_cicilan,
                'total_cicilan' => $item->total_cicilan,
                'sisa_pembayaran' => $item->sisa_pembayaran,
                'cicilan' => $item->cicilan,
                'siswa' => $item->siswa
            ];
        });

        return response()->json([
            'data' => $formattedData,
            'message' => 'Success get pembayaran data by jenis',
            'code' => 200
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pembayaran = Pembayaran::with(['siswa:id,nisn,nama_siswa,tahun_ajaran_id', 'cicilan'])->get();

        $pembayaran = $pembayaran->map(function ($item) {
            $item->append(['total_cicilan', 'sisa_pembayaran', 'status_cicilan']);

            $buktiPembayaranUrl = null;
            if ($item->bukti_pembayaran) {
                $buktiPembayaranUrl = Storage::url($item->bukti_pembayaran);
            }
            $item->bukti_pembayaran_url = $buktiPembayaranUrl;
            return $item;
        });

        return response()->json([
            'data' => $pembayaran,
            'message' => 'Success get pembayaran spp data',
            'code' => 200,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'siswa_id' => 'required|exists:siswa,id',
            'tanggal_pembayaran' => 'nullable|date',
            'bukti_pembayaran' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status_rapor' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0',
            'jenis_pembayaran' => 'nullable|in:pendaftaran baru,daftar ulang',
            'metode_pembayaran' => 'required|in:full,cicilan',
            'status_atribut' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $validatedData = $validator->validated();

            $isFullPayment = $validatedData['metode_pembayaran'] === 'full';
            $validatedData['status_pembayaran'] = $isFullPayment ? 'Lunas' : 'Belum Lunas';
            // $validatedData['status_cicilan'] = $isFullPayment ? 'Lunas' : 'Belum Lunas'; // Default status cicilan

            $isFullPayment = $validatedData['metode_pembayaran'] === 'full';
            $validatedData['status_pembayaran'] = $isFullPayment ? 'Lunas' : 'Belum Lunas';

            if ($request->hasFile('bukti_pembayaran')) {
                $path = $request->file('bukti_pembayaran')->store('images', 'public');
                $validatedData['bukti_pembayaran'] = $path;
            } else {
                $validatedData['bukti_pembayaran'] = null;
            }

            $pembayaran = Pembayaran::create($validatedData);

            DB::commit();

            $pembayaran->append(['total_cicilan', 'sisa_pembayaran', 'status_cicilan']);
            $pembayaran->bukti_pembayaran_url = $pembayaran->bukti_pembayaran ? Storage::url($pembayaran->bukti_pembayaran) : null;

            return response()->json([
                'message' => 'Pembayaran berhasil ditambahkan',
                'data' => $pembayaran,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data pembayaran',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pembayaran = Pembayaran::with(['cicilan', 'siswa'])->findOrFail($id);

        $pembayaran->append(['total_cicilan', 'sisa_pembayaran', 'status_cicilan']);

        $buktiPembayaranUrl = null;
        if ($pembayaran->bukti_pembayaran) {
            $buktiPembayaranUrl = Storage::url($pembayaran->bukti_pembayaran);
        }
        $pembayaran->bukti_pembayaran_url = $buktiPembayaranUrl;

        return response()->json([
            'data' => $pembayaran,
            'message' => 'Success get pembayaran data',
            'code' => 200,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $pembayaran = Pembayaran::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'siswa_id' => 'required|exists:siswa,id',
            'tanggal_pembayaran' => 'nullable|date',
            'bukti_pembayaran' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status_rapor' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0',
            'jenis_pembayaran' => 'nullable|string|max:255',
            'status_atribut' => 'nullable|string|max:255',
            'metode_pembayaran' => 'sometimes|in:full,cicilan',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();

            if ($request->hasFile('bukti_pembayaran')) {
                if ($pembayaran->bukti_pembayaran && Storage::disk('public')->exists($pembayaran->bukti_pembayaran)) {
                    Storage::disk('public')->delete($pembayaran->bukti_pembayaran);
                }
                $path = $request->file('bukti_pembayaran')->store('images', 'public');
                $validatedData['bukti_pembayaran'] = $path;
            } else if ($request->input('bukti_pembayaran') === null || $request->input('bukti_pembayaran') === '') {
                if ($pembayaran->bukti_pembayaran && Storage::disk('public')->exists($pembayaran->bukti_pembayaran)) {
                    Storage::disk('public')->delete($pembayaran->bukti_pembayaran);
                }
                $validatedData['bukti_pembayaran'] = null;
            } else {
                unset($validatedData['bukti_pembayaran']);
            }

            if (isset($validatedData['metode_pembayaran'])) {
                $pembayaran->metode_pembayaran = $validatedData['metode_pembayaran'];

                $pembayaran->status_pembayaran = $pembayaran->metode_pembayaran === 'full' ? 'Lunas' : 'Belum Lunas';
                // $pembayaran->status_cicilan = $pembayaran->metode_pembayaran === 'full' ? 'Lunas' : 'Belum Lunas';
            }

            $pembayaran->fill($validatedData);

            $pembayaran->save();
            DB::commit();

            $pembayaran->append(['total_cicilan', 'sisa_pembayaran', 'status_cicilan']);
            $pembayaran->bukti_pembayaran_url = $pembayaran->bukti_pembayaran ? Storage::url($pembayaran->bukti_pembayaran) : null;

            return response()->json([
                'data' => $pembayaran,
                'message' => 'Pembayaran berhasil diupdate',
                'code' => 200,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error saat mengupdate pembayaran: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $pembayaran = Pembayaran::findOrFail($id);

            if ($pembayaran->bukti_pembayaran && Storage::disk('public')->exists($pembayaran->bukti_pembayaran)) {
                Storage::disk('public')->delete($pembayaran->bukti_pembayaran);
            }

            if ($pembayaran->cicilan()->count() > 0) {
                $pembayaran->cicilan()->delete();
            }

            $pembayaran->delete();

            DB::commit();

            return response()->json([
                'message' => 'Pembayaran berhasil dihapus',
                'code' => 200,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error saat menghapus pembayaran: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }
}