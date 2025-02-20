<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Admin;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    // Menghitung jumlah siswa per kelas
    public function jumlahSiswaPerKelas($id)
    {
        $kelas = Kelas::withCount('siswa')->findOrFail($id);

        return response()->json([
            'kelas' => $kelas->nama_kelas, // Pastikan nama_kelas ada di database
            'jumlah_siswa' => $kelas->siswa_count,
            'message' => 'Jumlah siswa dalam kelas berhasil ditampilkan',
            'code' => 200,
        ]);
    }

    // Menampilkan semua data kelas
    public function index()
    {
        // Mengambil semua kelas + jumlah siswa
        $kelas = Kelas::withCount('siswa')->paginate(10); // Menggunakan pagination

        return response()->json([
            'data' => $kelas,
            'message' => 'Success get class',
            'code' => 200,
        ]);
    }

    // Menyimpan data kelas baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kelas' => 'required|string|max:255',
        ]);

        $kelas = Kelas::create($validated);

        return response()->json([
            'data' => $kelas,
            'message' => 'Class successfully created',
            'code' => 201,
        ], 201);
    }

    // Update data kelas
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
