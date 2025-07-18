<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\Cicilan;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;

class PembayaranController extends Controller
{
    private function uploadToSupabase($file, $folderName)
    {
        $bucket = 'images';
        $fileName = $file->hashName(); // tetap acak supaya unik
        $path = "pembayaran/{$folderName}/{$fileName}"; // path folder: berdasarkan nama siswa

        $response = Http::withHeaders([
            'apikey' => env('SUPABASE_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_KEY'),
            'Content-Type' => $file->getMimeType(),
        ])->put(
            env('SUPABASE_URL') . "/storage/v1/object/$bucket/$path",
            file_get_contents($file)
        );

        if ($response->successful()) {
            return env('SUPABASE_URL') . "/storage/v1/object/public/$bucket/$path";
        }

        return null;
}

    public function exportPembayaran(Request $request)
{
    try {
        $request->validate([
            'kelas_ids' => 'sometimes|array',
            'kelas_ids.*' => 'integer',
            'metode_pembayaran' => 'sometimes|string',
            'status_pembayaran' => 'sometimes|string',
            'status_cicilan' => 'sometimes|string',
            'tahun_awal' => 'sometimes|integer',
            'tahun_akhir' => 'sometimes|integer'
        ]);

        $query = Pembayaran::with(['siswa.kelas', 'siswa.tahunAjaran',  'cicilan' => function($q) {
            $q->where('status_verifikasi', 'disetujui')
              ->orderBy('tanggal_cicilan', 'asc');
        }])
            ->when($request->filled('kelas_ids'), function ($q) use ($request) {
                $kelasIds = is_array($request->kelas_ids) ? $request->kelas_ids : explode(',', $request->kelas_ids);
                $q->whereHas('siswa', function ($s) use ($kelasIds) {
                    $s->whereIn('kelas_id', $kelasIds);
                });
            })
            ->when($request->filled('metode_pembayaran'), function ($q) use ($request) {
                $q->where('metode_pembayaran', $request->metode_pembayaran);
            })
            ->when($request->filled('status_pembayaran'), function ($q) use ($request) {
                $q->where('status_pembayaran', $request->status_pembayaran);
            })
            ->when($request->filled('status_cicilan'), function ($q) use ($request) {
                $q->where('status_cicilan', $request->status_cicilan);
            })
            ->when($request->filled('tahun_awal') && $request->filled('tahun_akhir'), function ($q) use ($request) {
                $q->whereHas('siswa.tahunAjaran', function ($ta) use ($request) {
                    $ta->whereBetween(DB::raw("SUBSTRING_INDEX(tahun, '/', 1)"), [$request->tahun_awal, $request->tahun_akhir]);
                });
            });

        $data = $query->get()->map(function ($item) {
            $item->append(['total_cicilan', 'sisa_pembayaran', 'status_cicilan']);

            $buktiPembayaranUrl = null;
            if ($item->bukti_pembayaran) {
                $buktiPembayaranUrl = Storage::url($item->bukti_pembayaran);
            }

            return [
                'id' => $item->id,
                'siswa_id' => $item->siswa_id,
                'nisn' => $item->siswa->nisn ?? '-',
                'nama_siswa' => $item->siswa->nama_siswa ?? $item->nama_siswa ?? '-',
                'kelas_nama' => $item->siswa->kelas->nama_kelas ?? '-',
                'tahun_ajaran' => $item->siswa->tahunAjaran->tahun ?? '-',
                'tanggal_bayar' => $item->tanggal_pembayaran,
                'jenis_pembayaran' => $item->jenis_pembayaran,
                'metode_pembayaran' => $item->metode_pembayaran,
                'status_pembayaran' => $item->status_pembayaran,
                'status_rapor' => $item->status_rapor,
                'status_atribut' => $item->status_atribut,
                'status_cicilan' => $item->status_cicilan,
                'nominal' => $item->nominal,
                'total_cicilan' => $item->total_cicilan,
                'sisa_pembayaran' => $item->sisa_pembayaran,
                'bukti_pembayaran' => $item->bukti_pembayaran,
                'cicilan' => $item->cicilan
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Data pembayaran berhasil difilter'
        ]);
    } catch (\Exception $e) {
        \Log::error('Export Pembayaran Error: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan saat mengambil data pembayaran: ' . $e->getMessage()
        ], 500);
    }
}

public function getPembayaran(Request $request)
{
    $query = Pembayaran::with([
        'siswa:id,nisn,nama_siswa,tahun_ajaran_id,kelas_id',
        'siswa.tahunAjaran:id,tahun',
        'siswa.kelas:id,nama_kelas',
        'cicilan'
    ]);

    $query->when($request->filled('kelas_ids'), function ($q) use ($request) {
        $kelasIds = is_array($request->kelas_ids) ? $request->kelas_ids : explode(',', $request->kelas_ids);
        $q->whereHas('siswa', function ($sub) use ($kelasIds) {
            $sub->whereIn('kelas_id', $kelasIds);
        });
    });

    $query->when($request->filled('status_pembayaran'), function ($q) use ($request) {
        $q->where('status_pembayaran', $request->status_pembayaran);
    });

    $query->when($request->filled('metode_pembayaran'), function ($q) use ($request) {
        $q->where('metode_pembayaran', $request->metode_pembayaran);
    });

    $query->when($request->filled('tahun_ajaran_dari') && $request->filled('tahun_ajaran_sampai'), function ($q) use ($request) {
        $q->whereHas('siswa.tahunAjaran', function ($sub) use ($request) {
            $sub->whereRaw("CAST(SUBSTRING_INDEX(tahun, '/', 1) AS UNSIGNED) >= ?", [$request->tahun_ajaran_dari])
                ->whereRaw("CAST(SUBSTRING_INDEX(tahun, '/', -1) AS UNSIGNED) <= ?", [$request->tahun_ajaran_sampai]);
        });
    });

    $result = $query->get();

    if ($request->filled('status_cicilan')) {
        $result = $result->filter(function ($item) use ($request) {
            $item->append(['status_cicilan']);
            return strtolower($item->status_cicilan) === strtolower($request->status_cicilan);
        })->values();
    }

    return response()->json([
        'success' => true,
        'data' => $result
    ]);
}

    /**
     * Display the specified payment with installments for student.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showByPembayaranIdSiswa($id)
    {
        $pembayaran = Pembayaran::with(['siswa', 'cicilan' => function ($query) {
            $query->orderBy('tanggal_cicilan', 'asc');
        }])->where('siswa_id', $id)->get();

        $formattedData = $pembayaran->map(function ($item) {
            $item->append(['total_cicilan', 'sisa_pembayaran', 'status_cicilan']);

            $buktiPembayaranUrl = null;
            if ($item->bukti_pembayaran) {
                $buktiPembayaranUrl = Storage::url($item->bukti_pembayaran);
            }

            return [
                'id_pembayaran' => $item->id,
                'siswa_id' => $item->siswa_id,
                'tanggal_pembayaran' => $item->tanggal_pembayaran,
                'status_pembayaran' => $item->status_pembayaran,
                'status_rapor' => $item->status_rapor,
                'nominal' => $item->nominal,
                'metode_pembayaran' => $item->metode_pembayaran,
                'jenis_pembayaran' => $item->jenis_pembayaran,
                'status_atribut' => $item->status_atribut,
                'bukti_pembayaran' => $item->bukti_pembayaran,
                'bukti_pembayaran_url' => $buktiPembayaranUrl,
                'status_cicilan' => $item->status_cicilan,
                'total_cicilan' => $item->total_cicilan,
                'sisa_pembayaran' => $item->sisa_pembayaran,
                'cicilan' => $item->cicilan,
                'siswa' => $item->siswa
            ];
        });

        return response()->json($formattedData);
    }

    public function showByJenisPembayaran($idSiswa, $jenis)
    {
        $user = auth()->user();
        if ($user->id != $idSiswa) {
            return response()->json([
                'message' => 'Anda hanya bisa mengakses data pembayaran sendiri',
                'code' => 403
            ], 403);
        }

        $jenis = strtolower(str_replace(' ', '_', $jenis));

        $validJenis = ['pendaftaran_baru', 'daftar_ulang'];
        if (!in_array($jenis, $validJenis)) {
            return response()->json([
                'message' => 'Jenis pembayaran tidak valid. Pilih: pendaftaran_baru atau daftar_ulang',
                'code' => 400
            ], 400);
        }

        $dbJenis = str_replace('_', ' ', $jenis);

        $pembayaran = Pembayaran::with([
                'siswa:id,nisn,nama_siswa,tahun_ajaran_id',
                'siswa.tahunAjaran:id,tahun',
                'cicilan:id,pembayaran_id,nominal_cicilan,tanggal_cicilan,status_verifikasi'
            ])
            ->where('siswa_id', $user->siswa_id)
            ->where('jenis_pembayaran', $dbJenis)
            ->get();

        if ($pembayaran->isEmpty()) {
            return response()->json([
                'message' => 'Data pembayaran tidak ditemukan',
                'code' => 404
            ], 404);
        }

        $formattedData = $pembayaran->map(function ($item) {
            $item->append(['total_cicilan', 'sisa_pembayaran', 'status_cicilan']);

            $buktiPembayaranUrl = null;
            if ($item->bukti_pembayaran) {
                $buktiPembayaranUrl = Storage::url($item->bukti_pembayaran);
            }

            return [
                'id_pembayaran' => $item->id,
                'siswa_id' => $item->siswa_id,
                'tanggal_pembayaran' => $item->tanggal_pembayaran,
                'status_pembayaran' => $item->status_pembayaran,
                'status_rapor' => $item->status_rapor,
                'metode_pembayaran' => $item->metode_pembayaran,
                'nominal' => $item->nominal,
                'jenis_pembayaran' => $item->jenis_pembayaran,
                'status_atribut' => $item->status_atribut,
                'bukti_pembayaran' => $item->bukti_pembayaran,
                'bukti_pembayaran_url' => $buktiPembayaranUrl,
                'status_cicilan' => $item->status_cicilan,
                'total_cicilan' => $item->total_cicilan,
                'sisa_pembayaran' => $item->sisa_pembayaran,
                'cicilan' => $item->cicilan,
                'siswa' => $item->siswa
            ];
        });

        return response()->json([
            'data' => $formattedData,
            'message' => 'Success get pembayaran data by jenis',
            'code' => 200
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pembayaran = Pembayaran::with(['siswa:id,nisn,nama_siswa,tahun_ajaran_id', 'cicilan'])->get();

        $pembayaran = $pembayaran->map(function ($item) {
            $item->append(['total_cicilan', 'sisa_pembayaran', 'status_cicilan']);

            $buktiPembayaranUrl = null;
            if ($item->bukti_pembayaran) {
                $buktiPembayaranUrl = Storage::url($item->bukti_pembayaran);
            }
            $item->bukti_pembayaran_url = $buktiPembayaranUrl;
            return $item;
        });

        return response()->json([
            'data' => $pembayaran,
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

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $siswa = Siswa::find($request->siswa_id);
        $tahunAjaranId = $siswa ? $siswa->tahun_ajaran_id : null;

        $validator = Validator::make($request->all(), [
            'siswa_id' => [
                'required',
                'exists:siswa,id',
                Rule::unique('pembayaran')->where(function ($query) use ($request, $tahunAjaranId) {
                    return $query->where('siswa_id', $request->siswa_id)
                                ->where('jenis_pembayaran', $request->jenis_pembayaran);
                })->whereNotNull('jenis_pembayaran')
            ],
            'tanggal_pembayaran' => 'nullable|date',
            'bukti_pembayaran' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status_rapor' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0',
            'jenis_pembayaran' => 'nullable|in:pendaftaran baru,daftar ulang',
            'metode_pembayaran' => 'required|in:full,cicilan',
            'status_atribut' => 'nullable|string|max:255',
        ], [
            'siswa_id.unique' => 'Pembayaran dengan jenis ini untuk siswa yang sama sudah ada.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Duplikasi Data',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $validatedData = $validator->validated();

            $isFullPayment = $validatedData['metode_pembayaran'] === 'full';
            $validatedData['status_pembayaran'] = $isFullPayment ? 'Lunas' : 'Belum Lunas';

            // Simpan file dengan folder nama siswa
            if ($request->hasFile('bukti_pembayaran')) {
                $namaSiswa = Str::slug($siswa->nama, '_'); // buat nama folder aman untuk nama file
                $folderPath = "images/pembayaran/{$namaSiswa}";
                $path = $request->file('bukti_pembayaran')->store($folderPath, 'public');
                $validatedData['bukti_pembayaran'] = $path;
            } else {
                $validatedData['bukti_pembayaran'] = null;
            }

            $pembayaran = Pembayaran::create($validatedData);

            DB::commit();

            $pembayaran->append(['total_cicilan', 'sisa_pembayaran', 'status_cicilan']);
            $pembayaran->bukti_pembayaran_url = $pembayaran->bukti_pembayaran ? Storage::url($pembayaran->bukti_pembayaran) : null;

            return response()->json([
                'message' => 'Pembayaran berhasil ditambahkan',
                'data' => $pembayaran,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Terjadi kesalahan saat menyimpan data pembayaran',
                'error' => $e->getMessage()
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
        $pembayaran = Pembayaran::with(['cicilan', 'siswa'])->findOrFail($id);

        $pembayaran->append(['total_cicilan', 'sisa_pembayaran', 'status_cicilan']);

        $buktiPembayaranUrl = null;
        if ($pembayaran->bukti_pembayaran) {
            $buktiPembayaranUrl = Storage::url($pembayaran->bukti_pembayaran);
        }
        $pembayaran->bukti_pembayaran_url = $buktiPembayaranUrl;

        return response()->json([
            'data' => $pembayaran,
            'message' => 'Success get pembayaran data',
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
        $pembayaran = Pembayaran::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'siswa_id' => 'required|exists:siswa,id',
            'tanggal_pembayaran' => 'nullable|date',
            'bukti_pembayaran' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'status_rapor' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0',
            'jenis_pembayaran' => 'nullable|string|max:255',
            'status_atribut' => 'nullable|string|max:255',
            'metode_pembayaran' => 'sometimes|in:full,cicilan',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $validatedData = $validator->validated();

            if ($request->hasFile('bukti_pembayaran')) {
                if ($pembayaran->bukti_pembayaran && Storage::disk('public')->exists($pembayaran->bukti_pembayaran)) {
                    Storage::disk('public')->delete($pembayaran->bukti_pembayaran);
                }
                $path = $request->file('bukti_pembayaran')->store('images', 'public');
                $validatedData['bukti_pembayaran'] = $path;
            } else if ($request->input('bukti_pembayaran') === null || $request->input('bukti_pembayaran') === '') {
                if ($pembayaran->bukti_pembayaran && Storage::disk('public')->exists($pembayaran->bukti_pembayaran)) {
                    Storage::disk('public')->delete($pembayaran->bukti_pembayaran);
                }
                $validatedData['bukti_pembayaran'] = null;
            } else {
                unset($validatedData['bukti_pembayaran']);
            }

            if (isset($validatedData['metode_pembayaran'])) {
                $pembayaran->metode_pembayaran = $validatedData['metode_pembayaran'];

                $pembayaran->status_pembayaran = $pembayaran->metode_pembayaran === 'full' ? 'Lunas' : 'Belum Lunas';
                // $pembayaran->status_cicilan = $pembayaran->metode_pembayaran === 'full' ? 'Lunas' : 'Belum Lunas';
            }

            $pembayaran->fill($validatedData);

            $pembayaran->save();
            DB::commit();

            $pembayaran->append(['total_cicilan', 'sisa_pembayaran', 'status_cicilan']);
            $pembayaran->bukti_pembayaran_url = $pembayaran->bukti_pembayaran ? Storage::url($pembayaran->bukti_pembayaran) : null;

            return response()->json([
                'data' => $pembayaran,
                'message' => 'Pembayaran berhasil diupdate',
                'code' => 200,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error saat mengupdate pembayaran: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
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
            $pembayaran = Pembayaran::findOrFail($id);

            if ($pembayaran->bukti_pembayaran && Storage::disk('public')->exists($pembayaran->bukti_pembayaran)) {
                Storage::disk('public')->delete($pembayaran->bukti_pembayaran);
            }

            if ($pembayaran->cicilan()->count() > 0) {
                $pembayaran->cicilan()->delete();
            }

            $pembayaran->delete();

            DB::commit();

            return response()->json([
                'message' => 'Pembayaran berhasil dihapus',
                'code' => 200,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error saat menghapus pembayaran: ' . $e->getMessage(),
                'code' => 500
            ], 500);
        }
    }
}
