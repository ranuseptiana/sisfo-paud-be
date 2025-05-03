<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use Illuminate\Http\Request;

class TahunAjaranController extends Controller
{
    public function index()
    {
        return response()->json(TahunAjaran::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'tahun' => 'required|unique:tahun_ajaran,tahun',
            'aktif' => 'boolean',
        ]);

        TahunAjaran::create($request->all());

        return response()->json(['message' => 'Tahun ajaran berhasil ditambahkan'], 201);
    }

    public function update(Request $request, $id)
    {
        $tahunAjaran = TahunAjaran::findOrFail($id);
        $tahunAjaran->update($request->all());

        return response()->json(['message' => 'Tahun ajaran diperbarui']);
    }

    public function destroy($id)
    {
        TahunAjaran::destroy($id);
        return response()->json(['message' => 'Tahun ajaran dihapus']);
    }
}

