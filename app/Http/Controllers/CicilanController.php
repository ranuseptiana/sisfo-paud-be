<?php

namespace App\Http\Controllers;

use App\Models\Cicilan;
use App\Models\Admin;
use App\Models\Pembayaran;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CicilanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // $cicilan = Cicilan::all();
        $cicilan = Cicilan::with('pembayaran.siswa')->get();
        return response ()->json([
            'data' => $cicilan,
            'message' => 'Data Cicilan Berhasil Ditampilkan',
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
            'pembayaran_id' => 'required|exists:pembayaran,id',
            'nominal_cicilan' => 'required|numeric|min:1',
            'tanggal_cicilan' => 'required|date',
            'status_verifikasi' => 'required|in:pending,disetujui,ditolak',
            'tempat_tagihan' => 'required|string',
            'keterangan' => 'required|string',
            // 'admin_id' => 'required|exists:admin,id',
        ]);

        DB::beginTransaction();

        try {
            $cicilan = Cicilan::create([
                'pembayaran_id' => $validated['pembayaran_id'],
                'nominal_cicilan' => $validated['nominal_cicilan'],
                'tanggal_cicilan' => $validated['tanggal_cicilan'],
                'status_verifikasi' => $validated['status_verifikasi'],
                'tempat_tagihan' => $validated['tempat_tagihan'],
                'keterangan' => $validated['keterangan']
            ]);

            $this->updatePaymentStatus($validated['pembayaran_id']);
            $pembayaran = Pembayaran::find($validated['pembayaran_id']);
            $pembayaran->touch();

            DB::commit();

                return response()->json([
                    'data' => $cicilan,
                    'message' => 'Cicilan berhasil ditambahkan',
                    'code' => 201,
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Error: ' . $e->getMessage(),
                    'code' => 500
                ], 500);
            }
    }

    private function checkPaymentCompletion($pembayaranId)
    {
        $pembayaran = Pembayaran::with('cicilan')->findOrFail($pembayaranId);
        // $totalCicilan = $pembayaran->cicilan->sum('nominal_cicilan');

        if ($totalCicilan >= $pembayaran->nominal) {
            $pembayaran->update([
                'status_pembayaran' => 'Lunas',
                'keterangan' => 'Lunas melalui cicilan'
            ]);
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
        $cicilan = Cicilan::with('pembayaran.siswa')->find($id);

        if (!$cicilan) {
            return response()->json([
                'message' => 'Cicilan Tidak Ditemukan',
                'code' => 404,
            ], 404);
        }

        $formattedCicilan = [
            'id' => $cicilan->id,
            'pembayaran_id' => $cicilan->pembayaran_id,
            'tanggal_cicilan' => $cicilan->tanggal_cicilan,
            'nominal_cicilan' => $cicilan->nominal_cicilan,
            'status_verifikasi' => $cicilan->status_verifikasi,
            'tempat_tagihan' => $cicilan->tempat_tagihan,
            'keterangan' => $cicilan->keterangan,
            'siswa_nama' => $cicilan->pembayaran->siswa->nama_siswa ?? null,
            'siswa_nisn' => $cicilan->pembayaran->siswa->nisn ?? null,
            'pembayaran_nominal_pokok' => $cicilan->pembayaran->nominal,
        ];

        return response()->json([
            'data' => $formattedCicilan,
            'message' => 'Data Cicilan Berhasil Ditampilkan',
            'code' => 200,
        ]);
    }

    public function showByPembayaranId($pembayaranId)
    {
        $cicilanList = Cicilan::with('pembayaran.siswa')
            ->where('pembayaran_id', $pembayaranId)
            ->get();

        if ($cicilanList->isEmpty()) {
            $pembayaran = Pembayaran::with('siswa')->find($pembayaranId);
            if ($pembayaran) {
                return response()->json([
                    'data' => [],
                    'siswa_nama' => $pembayaran->siswa->nama_siswa ?? 'Nama Siswa Tidak Ditemukan',
                    'message' => 'Tidak ada cicilan untuk pembayaran ini, tetapi data siswa ditemukan.',
                    'code' => 200,
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Pembayaran atau Cicilan Tidak Ditemukan',
                    'code' => 404,
                ], 404);
            }
        }

        $formattedData = $cicilanList->map(function ($cicilan) {
            return [
                'id' => $cicilan->id,
                'pembayaran_id' => $cicilan->pembayaran_id,
                'tanggal_cicilan' => $cicilan->tanggal_cicilan,
                'nominal_cicilan' => $cicilan->nominal_cicilan,
                'status_verifikasi' => $cicilan->status_verifikasi,
                'tempat_tagihan' => $cicilan->tempat_tagihan,
                'keterangan' => $cicilan->keterangan,
                'nama_siswa' => $cicilan->pembayaran->siswa->nama_siswa ?? null,
            ];
        });

        return response()->json([
            'data' => $formattedData,
            'message' => 'Data Cicilan Berhasil Ditampilkan',
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
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'pembayaran_id' => 'required|exists:pembayaran,id',
            'nominal_cicilan' => 'required|numeric',
            'tanggal_cicilan' => 'required|date',
            'status_verifikasi' => 'required|in:pending,disetujui,ditolak',
            'tempat_tagihan' => 'required|string',
            'keterangan' => 'required|string',
            // 'admin_id' => 'required|exists:admin,id',
        ]);

        // $cicilan = Cicilan::findOrFail($id);
        // $cicilan->update($validated);

        // return response()->json([
        //     'data' => $cicilan,
        //     'message' => 'Data cicilan berhasil diperbarui',
        //     'code' => 200,
        // ]);
        DB::beginTransaction();

        try {
            $cicilan = Cicilan::findOrFail($id);
            $cicilan->update([
                'nominal_cicilan' => $validated['nominal_cicilan'],
                'tanggal_cicilan' => $validated['tanggal_cicilan'],
                'status_verifikasi' => $validated['status_verifikasi'],
                'tempat_tagihan' => $validated['tempat_tagihan'],
                'keterangan' => $validated['keterangan'],
                // 'admin_id' => $validated['admin_id'],
            ]);

            $pembayaran = Pembayaran::find($validated['pembayaran_id']);
            $pembayaran->touch();

            DB::commit();

            return response()->json([
                'data' => $cicilan,
                'message' => 'Data cicilan berhasil diperbarui',
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
            $cicilan = Cicilan::findOrFail($id);
            $pembayaranId = $cicilan->pembayaran_id;
            $cicilan->delete();

            $this->updatePaymentStatus($pembayaranId);

            $pembayaran = Pembayaran::find($pembayaranId);
            if ($pembayaran) {
                $pembayaran->touch();
            }

            DB::commit();

            return response()->json([
                'message' => 'Cicilan berhasil dihapus',
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
     * Update payment status based on installments
     *
     * @param int $pembayaranId
     * @return void
     */
    private function updatePaymentStatus($pembayaranId)
    {
        $pembayaran = Pembayaran::with(['cicilan' => function($query) {
            $query->where('status_verifikasi', 'disetujui');
        }])->findOrFail($pembayaranId);

        $totalCicilan = $pembayaran->cicilan->sum('nominal_cicilan');
        $isLunas = $totalCicilan >= $pembayaran->nominal;

        $pembayaran->update([
            'status_pembayaran' => $isLunas ? 'Lunas' : 'Belum Lunas'
        ]);
    }
}
