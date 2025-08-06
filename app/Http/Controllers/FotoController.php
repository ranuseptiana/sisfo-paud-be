<?php

namespace App\Http\Controllers;

use App\Models\Foto;
use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\StreamInterface;
use GuzzleHttp\Psr7\Utils;

class FotoController extends Controller
{
    private function uploadToSupabase($file, $albumName)
    {
        try {
            if (!$file || !$file->isValid()) {
                Log::error('Invalid file');
                return null;
            }

            $bucket = env('SUPABASE_BUCKET', 'images');
            $fileName = Str::random(40) . '.' . $file->extension();
            $path = "album/{$albumName}/{$fileName}";
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

    public function index()
    {
        $foto = Foto::all();
        return response()->json([
            'data' => $foto,
            'message' => 'Data Foto Berhasil Ditampilkan',
            'code' => 200,
        ]);
    }

    public function getFotoByAlbum($albumId)
    {
        $fotos = Foto::where('album_id', $albumId)->get();
        return response()->json([
            'data' => $fotos,
            'message' => 'Foto berdasarkan album berhasil ditampilkan',
            'code' => 200,
        ]);
    }

    public function storeMultiple(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'album_id' => 'required|exists:album,id',
                'files.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
                'caption' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $album = Album::findOrFail($request->album_id);
            $albumNameSlug = Str::slug($album->nama_album);
            $uploadedPhotos = [];

            // Handle multiple file uploads
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $photoUrl = $this->uploadToSupabase($file, $albumNameSlug);

                    if (!$photoUrl) {
                        DB::rollBack();
                        return response()->json([
                            'message' => 'Gagal mengupload foto ke Supabase',
                            'code' => 500,
                        ], 500);
                    }

                    $foto = Foto::create([
                        'album_id' => $request->album_id,
                        'path_foto' => $photoUrl,
                        'caption' => $request->caption,
                    ]);

                    $uploadedPhotos[] = $foto;
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => count($uploadedPhotos) . ' foto berhasil disimpan',
                'data' => $uploadedPhotos,
                'code' => 201
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing multiple fotos: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan server',
                'error' => $e->getMessage(),
                'code' => 500,
            ], 500);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'album_id' => 'required|exists:album,id',
                'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
                'caption' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $album = Album::findOrFail($request->album_id);
            $albumNameSlug = Str::slug($album->nama_album);

            $photoUrl = null;
            if ($request->hasFile('file')) {
                $photoUrl = $this->uploadToSupabase($request->file('file'), $albumNameSlug);

                if (!$photoUrl) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Gagal mengupload foto ke Supabase',
                        'code' => 500,
                    ], 500);
                }
            }

            $foto = Foto::create([
                'album_id' => $request->album_id,
                'path_foto' => $photoUrl,
                'caption' => $request->caption,
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Foto berhasil disimpan',
                'data' => $foto,
                'code' => 201
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error storing foto: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan server',
                'error' => $e->getMessage(),
                'code' => 500,
            ], 500);
        }
    }

    public function show($id)
    {
        $foto = Foto::findOrFail($id);

        return response()->json([
            'data' => $foto,
            'message' => 'Data Foto Berhasil Ditampilkan',
            'code' => 200,
        ]);
    }

    public function update(Request $request, $id)
{
    $foto = Foto::findOrFail($id);

    $validator = Validator::make($request->all(), [
        'album_id' => 'required|exists:album,id',
        'file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:5120',
        'caption' => 'nullable|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'message' => 'Validasi gagal',
            'errors' => $validator->errors(),
        ], 422);
    }

    Log::info('Update Foto Request:', [
        'all' => $request->all(),
        'caption' => $request->caption,
        'has_caption' => $request->has('caption')
    ]);

    DB::beginTransaction();
    try {
        $data = $request->only(['album_id', 'caption']);

        if ($request->hasFile('file')) {
            $album = Album::findOrFail($request->album_id);
            $albumNameSlug = Str::slug($album->nama_album);
            $photoUrl = $this->uploadToSupabase($request->file('file'), $albumNameSlug);

            if (!$photoUrl) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Upload ke Supabase gagal',
                    'code' => 500,
                ], 500);
            }

            $data['path_foto'] = $photoUrl;
        }

        $foto->update($data);

        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Data Foto Berhasil Diperbarui',
            'data' => $foto,
            'code' => 200
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Error updating foto: ' . $e->getMessage());
        return response()->json([
            'message' => 'Terjadi kesalahan server',
            'error' => $e->getMessage(),
            'code' => 500,
        ], 500);
    }
}

    public function destroy($id)
    {
        $foto = Foto::find($id);

        if (!$foto) {
            return response()->json([
                'message' => 'Foto tidak ditemukan',
                'code' => 404,
            ], 404);
        }

        DB::beginTransaction();
        try {
            $foto->delete();
            DB::commit();

            return response()->json([
                'message' => 'Foto berhasil dihapus',
                'code' => 200,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting foto: ' . $e->getMessage());
            return response()->json([
                'message' => 'Terjadi kesalahan saat menghapus foto',
                'error' => $e->getMessage(),
                'code' => 500,
            ], 500);
        }
    }
}
