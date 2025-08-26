<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;
use App\Models\User;
use App\Imports\SiswaImport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SiswaController extends Controller
{
    public function importSiswa(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            $existingSiswaIds = Siswa::pluck('id')->toArray();

            Excel::import(new SiswaImport, $request->file('file'));

            $siswaBaru = Siswa::whereNotIn('id', $existingSiswaIds)
                            ->whereDoesntHave('user')
                            ->get();

                            foreach ($siswaBaru as $siswa) {
                                // ambil 2 kata awal dari nama
                                $namaArray = explode(' ', trim($siswa->nama_siswa));
                                $duaKataAwal = implode('', array_slice($namaArray, 0, 2));
                                $username = strtolower($duaKataAwal);

                                $nisn = $siswa->nisn ?? '123';
                                $lastThree = substr($nisn, -3);
                                $password = $username . $lastThree;

                                User::create([
                                    'name' => $siswa->nama_siswa,
                                    'username' => $username,
                                    'password' => Hash::make($password),
                                    'user_type' => 'siswa',
                                    'siswa_id' => $siswa->id,
                                ]);
                            }
            return response()->json(['message' => 'Import berhasil'], 200);

        } catch (\Throwable $e) {
            // Posisi 3: Debug error
            // dd($e->getMessage(), $e->getTrace()); // <-- Uncomment untuk debug error

            return response()->json(['message' => 'Import gagal: ' . $e->getMessage()], 500);
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
            $needsTahunLulusJoin = false;

            $allowedColumns = [
                'id', 'nisn', 'nipd', 'nama_siswa', 'nik_siswa', 'tanggal_lahir',
                'tempat_lahir', 'jenis_kelamin', 'agama', 'status', 'no_kk',
                'tahun_ajaran_id', 'tahun_lulus_id'
            ];

            foreach ($selectedColumns as $col) {
                if ($col === 'kelas_nama' || $col === 'rombel') {
                    $needsKelasJoin = true;
                    $columnsToSelect[] = 'kelas.nama_kelas as kelas_nama';
                } elseif ($col === 'tahun_ajaran_nama') {
                    $needsTahunAjaranJoin = true;
                    $columnsToSelect[] = 'tahun_ajaran_masuk.tahun as tahun_ajaran_nama';
                } elseif ($col === 'tahun_lulus_nama') {
                    $needsTahunLulusJoin = true;
                    $columnsToSelect[] = 'tahun_ajaran_lulus.tahun as tahun_lulus_nama';
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
                    'tahun_ajaran_masuk.tahun as tahun_ajaran_nama',
                    'tahun_ajaran_lulus.tahun as tahun_lulus_nama'
                ];
                $needsKelasJoin = true;
                $needsTahunAjaranJoin = true;
                $needsTahunLulusJoin = true;
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

            if ($request->filled('tahun_ajaran_id')) {
                $query->where('siswa.tahun_ajaran_id', $request->tahun_ajaran_id);
            }

            if ($request->filled('tahun_lulus_id')) {
                $query->where('siswa.tahun_lulus_id', $request->tahun_lulus_id);
            }

            if ($needsKelasJoin) {
                $query->leftJoin('kelas', 'siswa.kelas_id', '=', 'kelas.id');
            }

            if ($needsTahunAjaranJoin) {
                $query->leftJoin('tahun_ajaran as tahun_ajaran_masuk', 'siswa.tahun_ajaran_id', '=', 'tahun_ajaran_masuk.id');
            }

            if ($needsTahunLulusJoin) {
                $query->leftJoin('tahun_ajaran as tahun_ajaran_lulus', 'siswa.tahun_lulus_id', '=', 'tahun_ajaran_lulus.id');
            }

            if ($request->has('sort_by')) {
                $direction = $request->sort_order ?? 'asc';
                $query->orderBy($request->sort_by, $direction);
            }

            $data = $query->get()->map(function($item) {
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
        'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
        'tahun_lulus_id' => 'nullable|exists:tahun_ajaran,id'
    ]);

    $validated['tanggal_lahir'] = Carbon::parse($validated['tanggal_lahir'])->format('Y-m-d');

    // Mulai transaction untuk memastikan konsistensi data
    DB::beginTransaction();

    try {
        // Buat data siswa
        $siswa = Siswa::create($validated);

        // Buat user untuk siswa baru (logika sama seperti di import)
        // ambil 2 kata awal dari nama
        $namaArray = explode(' ', trim($siswa->nama_siswa));
        $duaKataAwal = implode('', array_slice($namaArray, 0, 2));
        $username = strtolower($duaKataAwal);

        // Cek jika username sudah ada, tambahkan angka unik
        $originalUsername = $username;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $originalUsername . $counter;
            $counter++;
        }

        $nisn = $siswa->nisn ?? '123';
        $lastThree = substr($nisn, -3);
        $password = $username . $lastThree;

        $user = User::create([
            'name' => $siswa->nama_siswa,
            'username' => $username,
            'password' => Hash::make($password),
            'user_type' => 'siswa',
            'siswa_id' => $siswa->id,
        ]);

        DB::commit();

        return response()->json([
            'data' => $siswa,
            'user' => [
                'username' => $user->username,
                'password' => 'username + 3 digit terakhir NISN'
            ],
            'message' => 'Data Siswa Berhasil Ditambahkan dan akun berhasil dibuat',
            'code' => 201,
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'message' => 'Gagal menambahkan data siswa: ' . $e->getMessage(),
            'code' => 500,
        ], 500);
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
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'tahun_lulus_id' => 'nullable|exists:tahun_ajaran,id'
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
    DB::beginTransaction();

    try {
        $siswa = Siswa::findOrFail($id);

        // Hapus user yang terkait dengan siswa
        User::where('siswa_id', $id)->delete();

        // Hapus siswa
        $siswa->delete();

        DB::commit();

        return response()->json([
            'message' => 'Data siswa dan akun terkait berhasil dihapus',
            'code' => 200,
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'message' => 'Gagal menghapus data siswa: ' . $e->getMessage(),
            'code' => 500,
        ], 500);
    }
}
}
