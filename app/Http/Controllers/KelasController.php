<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Admin;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    // Menampilkan semua data kelas
    public function index()
    {
        $kelas = Kelas::with('admin')->get(); // Mengambil data kelas beserta admin
        return response()->json([
            'data' => $kelas,
            'message'=>'success get class',
            'code'=>200,
        ]);

        $kelas = Kelas::with('admin')->paginate(10); // Paginasi kelas
        return KelasResource::collection($kelas);
    }

    // Menyimpan data kelas baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kelas' => 'required|string|max:255',
            // 'admin_id' => 'required|exists:admin,id', // Validasi admin ID harus ada di tabel admin
        ]);

        $kelas = Kelas::create($validated);

        return response()->json([
            'data' => $kelas,
            'message' => 'Class successfully created',
            'code' => 201,
        ], 201);
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
            'nama_kelas' => 'required|string|max:255'
        ]);

        $kelas = Kelas::findOrFail($id);
        $kelas->update($validated);

        return response()->json([
            'data' => $kelas,
            'message' => 'Class successfully updated',
            'code' => 200,
        ]);
    }

    // Menghapus data kelas
    public function destroy($id)
    {
        $kelas = Kelas::findOrFail($id);
        $kelas->delete();
        return response()->json([
            'message' => 'Class successfully deleted',
            'code' => 200,
        ]);
    }
}

