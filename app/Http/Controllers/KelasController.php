<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\User;
use App\Models\TahunAjaran;
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

    public function detailKelas($idSiswa)
    {
        $user = User::with(['siswa.kelas.waliKelas', 'siswa.kelas.siswa'])
                    ->where('id', $idSiswa)
                    ->first();

        if (!$user || !$user->siswa) {
            return response()->json([
                'message' => 'Siswa tidak ditemukan',
                'code' => 404,
            ], 404);
        }

        $kelas = $user->siswa->kelas;

        return response()->json([
            'user_name' => $user->name,
            'nama_siswa' => $user->siswa->nama_siswa,
            'siswa_data' => $user->siswa,
            'kelas' => $kelas,
            'wali_kelas' => $kelas->waliKelas,
            'daftar_siswa' => $kelas->siswa,
            'message' => 'Data kelas berhasil diambil',
            'code' => 200,
        ]);
    }

    public function daftarKelas($idGuru)
    {
        if (!is_numeric($idGuru)) {
            return response()->json([
                'message' => 'ID Guru tidak valid',
                'code' => 400,
            ], 400);
        }

        $user = User::with([
            'guru.kelas.siswa' => function($query) {
                $query->select('id', 'nisn', 'nama_siswa', 'jenis_kelamin', 'kelas_id');
            }
        ])
        ->where('id', $idGuru)
        ->first();

        if (!$user || !$user->guru) {
            return response()->json([
                'message' => 'Guru tidak ditemukan',
                'code' => 404,
            ], 404);
        }

        if (!$user->guru->kelas || $user->guru->kelas->isEmpty()) {
            return response()->json([
                'message' => 'Guru belum memiliki kelas',
                'code' => 404,
            ], 404);
        }

        $response = [
            'nama_guru' => $user->guru->nama_lengkap,
            'daftar_kelas' => $user->guru->kelas->map(function($kelas) {
                return [
                    'nama_kelas' => $kelas->nama_kelas,
                    'siswa' => $kelas->siswa->map(function($siswa) {
                        return [
                            'nisn' => $siswa->nisn,
                            'nama' => $siswa->nama_siswa,
                            'jenis_kelamin' => $siswa->jenis_kelamin,
                        ];
                    })
                ];
            })
        ];

        return response()->json([
            'data' => $response,
            'message' => 'Data kelas berhasil diambil',
            'code' => 200
        ]);
    }

    public function index()
    {
        $kelas = Kelas::leftJoin('relasi_kelas', 'kelas.id', '=', 'relasi_kelas.kelas_id')
        ->leftJoin('guru', 'relasi_kelas.guru_id', '=', 'guru.id')
        ->leftJoin('siswa', 'kelas.id', '=', 'siswa.kelas_id')
        ->select(
            'kelas.id',
            'kelas.nama_kelas',
            DB::raw("COALESCE(STRING_AGG(DISTINCT guru.nama_lengkap, ', '), '') as nama_guru"),
            DB::raw("(SELECT COUNT(*) FROM siswa WHERE siswa.kelas_id = kelas.id) as jumlah_siswa")
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
