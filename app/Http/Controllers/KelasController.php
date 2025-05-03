<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Guru;
use App\Models\RelasiKelas;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KelasController extends Controller
{
    // Menghitung jumlah siswa per kelas
    public function jumlahSiswaPerKelas($id)
    {
        $kelas = Kelas::withCount('siswa')->find($id);

        if (!$kelas) {
            return response()->json([
                'message' => 'Class not found',
                'code' => 404,
            ], 404);
        }

        return response()->json([
            'kelas' => $kelas->nama_kelas,
            'jumlah_siswa' => $kelas->siswa_count,
            'message' => 'Jumlah siswa dalam kelas berhasil ditampilkan',
            'code' => 200,
        ]);
    }

    // Menampilkan semua data kelas
    public function index()
    {
        $kelas = Kelas::leftJoin('relasi_kelas', 'kelas.id', '=', 'relasi_kelas.kelas_id')
        ->leftJoin('guru', 'relasi_kelas.guru_id', '=', 'guru.id')
        ->leftJoin('siswa', 'kelas.id', '=', 'siswa.kelas_id') // Tetap gabungkan siswa
        ->select(
            'kelas.id',
            'kelas.nama_kelas',
            DB::raw("COALESCE(STRING_AGG(DISTINCT guru.nama_lengkap, ', '), '') as nama_guru"), // DISTINCT untuk guru
            DB::raw("(SELECT COUNT(*) FROM siswa WHERE siswa.kelas_id = kelas.id) as jumlah_siswa") // Subquery untuk jumlah siswa
        )
        ->groupBy('kelas.id', 'kelas.nama_kelas')
        ->get();

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
            'guru_id' => 'required|exists:guru,id' // Validasi guru_id
        ]);

        DB::beginTransaction();
        try {
            // Buat kelas
            $kelas = Kelas::create(['nama_kelas' => $validated['nama_kelas']]);

            // Buat relasi dengan guru
            RelasiKelas::create([
                'kelas_id' => $kelas->id,
                'guru_id' => $validated['guru_id']
            ]);

            DB::commit();

            return response()->json([
                'data' => $kelas,
                'message' => 'Class successfully created',
                'code' => 201,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create class: ' . $e->getMessage(),
                'code' => 500,
            ], 500);
        }
    }

    // Update data kelas
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_kelas' => 'required|string|max:255',
            'guru_id' => 'required|exists:guru,id'
        ]);

        DB::beginTransaction();
        try {
            $kelas = Kelas::findOrFail($id);
            $kelas->update(['nama_kelas' => $validated['nama_kelas']]);

            // Update relasi guru (hapus yang lama, buat yang baru)
            RelasiKelas::where('kelas_id', $id)->delete();
            RelasiKelas::create([
                'kelas_id' => $id,
                'guru_id' => $validated['guru_id']
            ]);

            DB::commit();

            return response()->json([
                'data' => $kelas,
                'message' => 'Class successfully updated',
                'code' => 200,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update class: ' . $e->getMessage(),
                'code' => 500,
            ], 500);
        }
    }
    // Menghapus data kelas
    public function destroy($id)
{
    $kelas = Kelas::withCount('siswa')->findOrFail($id);

    if ($kelas->siswa_count > 0) {
        return response()->json([
            'message' => 'Cannot delete class, students are still registered in this class',
            'code' => 400,
        ], 400);
    }

    $kelas->delete();

    return response()->json([
        'message' => 'Class successfully deleted',
        'code' => 200,
    ]);
}

}
