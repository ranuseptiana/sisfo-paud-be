<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PembayaranSpp;

class PembayaranSppController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pembayaranspp = PembayaranSpp::with(['siswa:id,nama_siswa'])->get();
        return response()->json([
            'data' => $pembayaranspp,
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
            'status_pembayaran' => 'required|string|max:255',
            'status_rapor' => 'required|string|max:255',
            'nominal' => 'required|numeric'
        ]);

        $pembayaranspp = PembayaranSpp::create($validated);

        return response()->json([
            'data' => $pembayaranspp,
            'message' => 'Pembayaran SPP successfully created',
            'code' => 201,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pembayaranspp = PembayaranSpp::findOrFail($id);

        return response()->json([
            'data' => $pembayaranspp,
            'message' => 'Success get pembayaran spp data',
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
            'status_pembayaran' => 'required|string|max:255',
            'status_rapor' => 'required|string|max:255',
            'nominal' => 'required|numeric'
        ]);

        $pembayaranspp = PembayaranSpp::findOrFail($id);
        $pembayaranspp->update($validated);

        return response()->json([
            'data' => $pembayaranspp,
            'message' => 'Success get pembayaran spp data',
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
        $pembayaranspp = PembayaranSpp::findOrFail($id);
        $pembayaranspp->delete();

        return response()->json([
            'data' => $pembayaranspp,
            'message' => 'Success get pembayaran spp data',
            'code' => 200,
        ]);
    }
}
