<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RelasiKelas;

class RelasiKelasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = RelasiKelas::with(['siswa', 'kelas', 'guru'])->get();
        return response()->json([
            'data' => $data,
            'message' => 'Data Relasi Kelas berhasil ditampilkan',
            'code' => 200
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
            'kelas_id' => 'required|exists:kelas,id',
            'guru_id' => 'required|exists:guru,id',
        ]);

        $data = RelasiKelas::create($validated);

        return response()->json([
            'data' => $data,
            'message' => 'Data Relasi Kelas berhasil ditambahkan',
            'code' => 201
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
        $data = RelasiKelas::with(['siswa', 'kelas', 'guru'])->findOrFail($id);

        return response()->json([
            'data' => $data,
            'message' => 'Data Relasi Kelas berhasil ditampilkan',
            'code' => 200
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
            'siswa_id' => 'required|exists:siswa,id',
            'kelas_id' => 'required|exists:kelas,id',
            'guru_id' => 'required|exists:guru,id',
        ]);

        $data = RelasiKelas::findOrFail($id);
        $data->update($validated);

        return response()->json([
            'data' => $data,
            'message' => 'Data Relasi Kelas berhasil diupdate',
            'code' => 200
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
        $data = RelasiKelas::findOrFail($id);
        $data->delete();

        return response()->json([
            'data' => $data,
            'message' => 'Data Relasi Kelas berhasil dihapus',
            'code' => 200
        ]);
    }
}
