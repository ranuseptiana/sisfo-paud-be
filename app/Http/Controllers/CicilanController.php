<?php

namespace App\Http\Controllers;

use App\Models\Cicilan;
use App\Models\Admin;
use App\Models\Pembayaran;
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
        $cicilan = Cicilan::all();
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
            'admin_id' => 'required|exists:admin,id',
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
        $totalCicilan = $pembayaran->cicilan->sum('nominal_cicilan');

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
        $cicilan = Cicilan::where('pembayaran_id', $id)->get();

        if ($cicilan->isEmpty()) {
            return response()->json([
                'message' => 'Cicilan Tidak Ditemukan',
                'code' => 404,
            ]);
        }

        return response()->json([
            'data' => $cicilan,
            'message' => 'Data Cicilan Berhasil Ditampilkan',
            'code' => 200,
        ]);
    }

    public function showByPembayaranId($pembayaranId)
    {
        $cicilan = Cicilan::where('pembayaran_id', $pembayaranId)->get();

        if ($cicilan->isEmpty()) {
            return response()->json([
                'message' => 'Cicilan Tidak Ditemukan',
                'code' => 404,
            ], 404);
        }

        return response()->json([
            'data' => $cicilan,
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
            'admin_id' => 'required|exists:admin,id',
        ]);

        $cicilan = Cicilan::findOrFail($id);
        $cicilan->update($validated);

        return response()->json([
            'data' => $cicilan,
            'message' => 'Data cicilan berhasil diperbarui',
            'code' => 200,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $cicilan = Cicilan::findOrFail($id);
        $cicilan->delete();
        return response()->json([
            'message' => 'Cicilan berhasil dihapus',
            'code' => 200,
        ]);
    }
}
