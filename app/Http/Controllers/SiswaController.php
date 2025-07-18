<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Imports\SiswaImport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class SiswaController extends Controller
{
    public function importSiswa(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new SiswaImport, $request->file('file'));
            return response()->json(['message' => 'Import berhasil'], 200);

            if (count($import->failures) > 0) {
                return response()->json([
                    'message' => 'Beberapa baris gagal diimpor',
                    'errors' => $import->failures,
                ], 422);
            }

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal mengimpor data',
                'error' => $e->getMessage(),
            ], 500); // 500 error agar axios catch block terpanggil
        }
    }

    public function exportSiswa(Request $request)
    {
        try {
            $selectedColumns = $request->input('columns', []);
            if (!is_array($selectedColumns)) {
                $selectedColumns = [];
            }

            $columnsToSelect = [];
            $needsKelasJoin = false;
            $needsTahunAjaranJoin = false;

            $allowedColumns = [
                'id', 'nisn', 'nipd', 'nama_siswa', 'nik_siswa', 'tanggal_lahir',
                'tempat_lahir', 'jenis_kelamin', 'agama', 'status', 'no_kk'
            ];

            foreach ($selectedColumns as $col) {
                if ($col === 'kelas_nama' || $col === 'rombel') {
                    $needsKelasJoin = true;
                    $columnsToSelect[] = 'kelas.nama_kelas as kelas_nama';
                } elseif ($col === 'tahun_ajaran_nama') {
                    $needsTahunAjaranJoin = true;
                    $columnsToSelect[] = 'tahun_ajaran.tahun as tahun_ajaran_nama';
                } elseif (in_array($col, $allowedColumns)) {
                    $columnsToSelect[] = "siswa.$col";
                }
            }

            if (empty($columnsToSelect)) {
                $columnsToSelect = [
                    'siswa.id',
                    'siswa.nisn',
                    'siswa.nipd',
                    'siswa.nama_siswa',
                    'siswa.nik_siswa',
                    'siswa.tanggal_lahir',
                    'siswa.tempat_lahir',
                    'siswa.alamat',
                    'siswa.jenis_kelamin',
                    'siswa.agama',
                    'siswa.status',
                    'siswa.no_kk',
                    'siswa.anak_ke',
                    'siswa.jumlah_saudara',
                    'siswa.berat_badan',
                    'siswa.tinggi_badan',
                    'siswa.lingkar_kepala',
                    'kelas.nama_kelas as kelas_nama',
                    'tahun_ajaran.tahun as tahun_ajaran_nama'
                ];
                $needsKelasJoin = true;
                $needsTahunAjaranJoin = true;
            }

            // Inisialisasi query
            $query = Siswa::query()->select($columnsToSelect);

            // Terapkan filter
            if ($request->filled('status')) {
                $query->whereRaw('LOWER(siswa.status) = ?', [strtolower($request->status)]);
            }

            if ($request->filled('kelas_ids')) {
                $kelasIds = is_array($request->kelas_ids) ? $request->kelas_ids : explode(',', $request->kelas_ids);
                $query->whereIn('siswa.kelas_id', $kelasIds);
            }

            if ($needsKelasJoin) {
                $query->leftJoin('kelas', 'siswa.kelas_id', '=', 'kelas.id');
            }

            if ($needsTahunAjaranJoin) {
                $query->leftJoin('tahun_ajaran', 'siswa.tahun_ajaran_id', '=', 'tahun_ajaran.id');
            }

            $data = $query->get()->map(function($item) {
                // if (isset($item->tanggal_lahir) && $item->tanggal_lahir) {
                //     try {
                //         $item->tanggal_lahir = \Carbon\Carbon::parse($item->tanggal_lahir)->format('Y-m-d');
                //     } catch (\Exception $e) {
                //         $item->tanggal_lahir = null;
                //     }
                // }

                if (property_exists($item, 'tanggal_lahir') && $item->tanggal_lahir) {
                    try {
                        $item->tanggal_lahir = \Carbon\Carbon::parse($item->tanggal_lahir)->format('Y-m-d');
                    } catch (\Exception $e) {
                        $item->tanggal_lahir = null;
                    }
                }

                return $item;
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Data berhasil diambil'
            ]);

        } catch (\Exception $e) {
            \Log::error('Export error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $siswa = Siswa::all()->map(function ($item) {
            $item->tanggal_lahir = Carbon::parse($item->tanggal_lahir)->format('Y-m-d');
            return $item;
        });

        return response()->json([
            'data' => $siswa,
            'message' => 'Data Siswa Berhasil Ditampilkan',
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
            'no_kk' => 'required|exists:orangtua,no_kk',
            'nik_siswa' => 'required|unique:siswa,nik_siswa',
            'nipd' => 'nullable|string|max:9',
            'nisn' => 'nullable|string|max:10',
            'nama_siswa' => 'required|string|max:255',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|string|max:255',
            'agama' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
            'anak_ke' => 'required|integer',
            'jumlah_saudara' => 'required|integer' ,
            'berat_badan' => 'required|integer',
            'tinggi_badan' => 'required|integer',
            'lingkar_kepala' => 'nullable|integer',
            'kelas_id' => 'required|exists:kelas,id',
            'status' => 'required|string|max:255',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id'
        ]);

        $validated['tanggal_lahir'] = Carbon::parse($validated['tanggal_lahir'])->format('Y-m-d');

        $siswa = Siswa::create($validated);

        return response()->json([
            'data' => $siswa,
            'message' => 'Data Siswa Berhasil Ditambahkan',
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
        if ($id === 'export') {
            return $this->exportSiswa(request());
        }

        $siswa = Siswa::findOrFail($id);
        $siswa->tanggal_lahir = Carbon::parse($siswa->tanggal_lahir)->format('Y-m-d');

        return response()->json([
            'data' => $siswa,
            'message' => 'Data Siswa Berhasil Ditampilkan',
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
            'no_kk' => 'required|exists:orangtua,no_kk',
            'nik_siswa' => 'required|unique:siswa,nik_siswa,' . $id . ',id',
            'nipd' => 'nullable|string|max:9',
            'nisn' => 'nullable|string|max:10',
            'nama_siswa' => 'required|string|max:255',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|string|max:255',
            'agama' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
            'anak_ke' => 'required|integer',
            'jumlah_saudara' => 'required|integer' ,
            'berat_badan' => 'required|integer',
            'tinggi_badan' => 'required|integer',
            'lingkar_kepala' => 'required|integer',
            'kelas_id' => 'required|exists:kelas,id',
            'status' => 'required|string|max:255',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id'
        ]);

        $validated['tanggal_lahir'] = Carbon::parse($validated['tanggal_lahir'])->format('Y-m-d');

        $siswa = Siswa::findOrFail($id);
        $siswa->update($validated);

        return response()->json([
            'data' => $siswa,
            'message' => 'Data Siswa Berhasil Diperbarui',
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
        $siswa = Siswa::findOrFail($id);
        $siswa->delete();
        return response()->json([
            'message' => 'Data Siswa Berhasil Dihapus',
            'code' => 200,
        ]);
    }
}
