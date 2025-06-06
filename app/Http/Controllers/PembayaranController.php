<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\Cicilan;
use App\Models\User;
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
            $cicilan = $item->cicilan;

            $totalCicilan = $item->metode_pembayaran === 'cicilan'
                ? $cicilan->where('status_verifikasi', 'disetujui')->sum('nominal_cicilan')
                : $item->nominal;

            $sisaPembayaran = max(0, $item->nominal - $totalCicilan);

            $statusCicilan = $totalCicilan >= $item->nominal ? 'Lunas' : 'Belum Lunas';

            if ($item->status_pembayaran !== 'Lunas' && $statusCicilan === 'Lunas') {
                $item->status_pembayaran = 'Lunas';
                $item->save();
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
                'status_cicilan' => $statusCicilan,
                'total_cicilan' => $totalCicilan,
                'sisa_pembayaran' => $sisaPembayaran,
                'cicilan' => $cicilan,
                'siswa' => $item->siswa
            ];
        });

        return response()->json($formattedData);
    }

    public function showByJenisPembayaran($idSiswa, $jenis)
    {
        $user = auth()->user();

        if ($user->siswa_id != $idSiswa) {
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
            ->where('siswa_id', $idSiswa)
            ->where('jenis_pembayaran', $dbJenis)
            ->get();

        if ($pembayaran->isEmpty()) {
            return response()->json([
                'message' => 'Data pembayaran tidak ditemukan',
                'code' => 404
            ], 404);
        }

        $formattedData = $pembayaran->map(function ($item) {
            $cicilan = $item->cicilan;

            $totalCicilan = $item->metode_pembayaran === 'cicilan'
                ? $cicilan->where('status_verifikasi', 'disetujui')->sum('nominal_cicilan')
                : $item->nominal;

            $sisaPembayaran = max(0, $item->nominal - $totalCicilan);

            $statusCicilan = $totalCicilan >= $item->nominal ? 'Lunas' : 'Belum Lunas';

            // Auto-update status pembayaran jika cicilan sudah lunas
            if ($item->status_pembayaran !== 'Lunas' && $statusCicilan === 'Lunas') {
                $item->status_pembayaran = 'Lunas';
                $item->save();
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
                'status_cicilan' => $statusCicilan,
                'total_cicilan' => $totalCicilan,
                'sisa_pembayaran' => $sisaPembayaran,
                'cicilan' => $cicilan,
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
        $pembayaran = Pembayaran::with(['siswa:id,nisn,nama_siswa,tahun_ajaran_id'])->get();

        $pembayaran = $pembayaran->map(function ($item) {
            $totalCicilan = $item->metode_pembayaran === 'cicilan'
                ? $item->cicilan->where('status_verifikasi', 'disetujui')->sum('nominal_cicilan')
                : $item->nominal;

            $item->status_cicilan = $totalCicilan >= $item->nominal ? 'Lunas' : 'Belum Lunas';
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
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
        public function store(Request $request)
    {
        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'tanggal_pembayaran' => 'nullable|date',
            'bukti_pembayaran' => 'nullable|string|max:255',
            'status_rapor' => 'required|string|max:255',
            'nominal' => 'required|numeric',
            'jenis_pembayaran' => 'nullable|in:pendaftaran baru,daftar ulang',
            'metode_pembayaran' => 'required|in:full,cicilan',
            'status_atribut' => 'nullable|string|max:255',
            'status_pembayaran' => 'nullable|in:Lunas,Belum Lunas',
            'status_cicilan' => 'nullable|in:Lunas,Belum Lunas',
        ]);

        DB::beginTransaction();

        try {
            $isFullPayment = $validated['metode_pembayaran'] === 'full';

            if ($isFullPayment) {
                $validated['status_pembayaran'] = 'Lunas';
                $validated['status_cicilan'] = 'Lunas';
            }

            logger()->debug('isFullPayment:', ['value' => $isFullPayment]);

            $pembayaran = Pembayaran::create([
                'siswa_id' => $validated['siswa_id'],
                'tanggal_pembayaran' => $validated['tanggal_pembayaran'] ?? now(),
                'bukti_pembayaran' => $validated['bukti_pembayaran'] ?? null,
                'status_rapor' => $validated['status_rapor'],
                'status_atribut' => $validated['status_atribut'] ?? null,
                'nominal' => $validated['nominal'],
                'jenis_pembayaran' => $validated['jenis_pembayaran'] ?? null,
                'metode_pembayaran' => $validated['metode_pembayaran'],
                'status_pembayaran' => $isFullPayment ? 'Lunas' : 'Belum Lunas',
                'status_cicilan' => $isFullPayment ? 'Lunas' : 'Belum Lunas',

            ]);

            DB::commit();

            return response()->json([
                'message' => 'Pembayaran berhasil ditambahkan',
                'data' => $pembayaran
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

        $totalCicilan = $pembayaran->metode_pembayaran === 'cicilan'
            ? $pembayaran->cicilan->where('status_verifikasi', 'disetujui')->sum('nominal_cicilan')
            : $pembayaran->nominal;

        $statusCicilan = $totalCicilan >= $pembayaran->nominal ? 'Lunas' : 'Belum Lunas';

        // Sinkronisasi status_pembayaran dan status_cicilan
        if ($pembayaran->status_pembayaran !== 'Lunas' && $statusCicilan === 'Lunas') {
            $pembayaran->status_pembayaran = 'Lunas';
            $pembayaran->save();
        }

        $pembayaran->status_cicilan = $statusCicilan;

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
        //
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
        $validated = $request->validate([
            'tanggal_pembayaran' => 'nullable|date',
            'bukti_pembayaran' => 'nullable|string|max:255',
            'status_rapor' => 'required|string|max:255',
            'nominal' => 'required|numeric',
            'jenis_pembayaran' => 'nullable|string|max:255',
            'status_atribut' => 'nullable|string|max:255',
            'metode_pembayaran' => 'sometimes|in:full,cicilan',
        ]);

        $pembayaran = Pembayaran::findOrFail($id);

        DB::beginTransaction();
        try {
            // Update data dasar
            $pembayaran->update($validated);

            // Jika metode pembayaran diubah, update status pembayaran
            if (isset($validated['metode_pembayaran'])) {
                $pembayaran->status_pembayaran = $validated['metode_pembayaran'] === 'full'
                    ? 'Lunas'
                    : 'Belum Lunas';
                $pembayaran->save();
            }

            // Jika nominal diubah, perlu cek ulang status cicilan
            if (isset($validated['nominal'])) {
                $totalCicilan = $pembayaran->metode_pembayaran === 'cicilan'
                    ? $pembayaran->cicilan->where('status_verifikasi', 'disetujui')->sum('nominal_cicilan')
                    : $pembayaran->nominal;

                if ($totalCicilan >= $pembayaran->nominal) {
                    $pembayaran->status_pembayaran = 'Lunas';
                    $pembayaran->save();
                }
            }

            DB::commit();

            return response()->json([
                'data' => $pembayaran,
                'message' => 'Pembayaran berhasil diupdate',
                'code' => 200,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error: ' . $e->getMessage(),
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

            // Hapus cicilan terlebih dahulu jika ada
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
                'message' => 'Error: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }
}
