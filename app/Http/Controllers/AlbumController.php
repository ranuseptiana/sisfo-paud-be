<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Album;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\StreamInterface;
use GuzzleHttp\Psr7\Utils;

class AlbumController extends Controller
{
private function uploadToSupabase($file, $folderName)
{
    try {
        if (!$file || !$file->isValid()) {
            Log::error('Invalid file');
            return null;
        }

        $bucket = env('SUPABASE_BUCKET', 'images');
        $fileName = Str::random(40) . '.' . $file->extension();
        $path = "album/{$folderName}/{$fileName}";
        $uploadUrl = env('SUPABASE_STORAGE_URL') . "/object/{$bucket}/{$path}";

        $stream = \GuzzleHttp\Psr7\Utils::streamFor($file->get());

        $response = Http::withHeaders([
            'apikey' => env('SUPABASE_KEY'),
            'Authorization' => 'Bearer ' . env('SUPABASE_SECRET'),
            'Content-Type' => $file->getMimeType(),
        ])->withBody($stream, $file->getMimeType())
          ->put($uploadUrl);

        if ($response->successful()) {
            return env('SUPABASE_STORAGE_URL') . "/object/public/{$bucket}/{$path}";
        }

        Log::error('Upload gagal', ['status' => $response->status(), 'body' => $response->body()]);
        return null;

    } catch (\Exception $e) {
        Log::error('Exception upload: ' . $e->getMessage());
        return null;
    }
}

    public function indexWithFoto()
    {
        $albums = Album::with('foto')->get();

        return response()->json([
            'status' => 'success',
            'data' => $albums
        ]);
    }

    public function index()
    {
            $album = Album::all();
            return response()->json([
                'data' => $album,
                'message' => 'Data Album Berhasil Ditampilkan',
                'code' => 200,
            ]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'nama_album' => 'required|string|max:255',
                'deskripsi' => 'nullable|string',
                'lokasi_kegiatan' => 'nullable|string|max:255',
                'tanggal_kegiatan' => 'nullable|date',
                'photo_cover' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
                'admin_id' => 'nullable|integer',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Handle file upload
            $photoCoverUrl = null;
            if ($request->hasFile('photo_cover')) {
                $folderName = Str::slug($request->nama_album);
                $photoCoverUrl = $this->uploadToSupabase($request->file('photo_cover'), $folderName);

                if (!$photoCoverUrl) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Gagal mengupload photo cover ke Supabase',
                        'code' => 500,
                    ], 500);
                }
            }

            $album = new Album();
            $album->nama_album = $this->sanitizeString($request->nama_album);
            $album->deskripsi = $this->sanitizeString($request->deskripsi ?? '');
            $album->lokasi_kegiatan = $this->sanitizeString($request->lokasi_kegiatan ?? '');
            $album->tanggal_kegiatan = $request->tanggal_kegiatan;
            $album->photo_cover = $photoCoverUrl;
            $album->admin_id = $request->admin_id ?? auth()->id(); // Default ke user yang login
            $album->save();

            DB::commit();

            return response()->json([
                'message' => 'Album berhasil disimpan',
                'data' => $album,
                'code' => 201,
            ], 201, [], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing album: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan server',
                'error' => $e->getMessage(),
                'code' => 500,
            ], 500);
        }
    }

    private function sanitizeString($string)
    {
        if (is_null($string)) {
            return null;
        }

        $string = iconv('UTF-8', 'UTF-8//IGNORE', $string);

        if (mb_detect_encoding($string, 'UTF-8', true) === false) {
            $string = mb_convert_encoding($string, 'UTF-8');
        }

        return $string;
    }

    public function show($id)
    {
        $album = Album::findOrFail($id);

        return response()->json([
            'data' => $album,
            'message' => 'Data Album Berhasil Ditampilkan',
            'code' => 200,
        ]);
    }

    public function update(Request $request, $id)
    {
        $album = Album::findOrFail($id);

        $validated = $request->validate([
            'nama_album' => 'required|string|max:255',
            'deskripsi' => 'required|string|max:255',
            'tanggal_kegiatan' => 'nullable|date',
            'lokasi_kegiatan' => 'nullable|string|max:255',
            'photo_cover' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $validated;

        DB::beginTransaction();
        try {
            if ($request->hasFile('photo_cover')) {
                $folderName = Str::slug($request->nama_album);
                $coverUrl = $this->uploadToSupabase($request->file('photo_cover'), $folderName);

                if (!$coverUrl) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Upload ke Supabase gagal saat update',
                        'code' => 500,
                    ], 500);
                }

                $data['photo_cover'] = $coverUrl;
            }

            $album->update($data);

            DB::commit();

            // Pastikan mengembalikan data lengkap dengan URL baru
            return response()->json([
                'data' => $album->fresh(),
                'message' => 'Data Album Berhasil Diperbarui',
                'code' => 200,
            ], 200, [], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating album: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan server',
                'error' => $e->getMessage(),
                'code' => 500,
            ], 500);
        }
    }

    public function destroy($id)
    {
        $album = Album::find($id);

        if (!$album) {
            return response()->json([
                'message' => 'Album not found',
                'code' => 404,
            ], 404);
        }

        $album->delete();

        return response()->json([
            'message' => 'Album successfully deleted',
            'code' => 200,
        ]);
    }
}
