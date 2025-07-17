<?php

namespace App\Http\Controllers;

use App\Models\Foto;
use App\Models\Album;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class FotoController extends Controller
{
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
        return response()->json(['data' => $fotos]);
    }

    public function store(Request $request)
    {
        Log::info('Incoming Foto Store Request', $request->all());

        $validatedData = $request->validate([
            'album_id' => 'required|exists:album,id',
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'caption' => 'nullable|string|max:255',
        ]);

        try {
            $album = Album::findOrFail($validatedData['album_id']);
            $albumNameSlug = Str::slug($album->nama_album);
            $file = $request->file('file');
            $bucket = env('SUPABASE_BUCKET', 'images');
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = "images/album/{$albumNameSlug}/{$fileName}";

            // Upload ke Supabase Storage
            $response = Http::withHeaders([
                'apikey' => env('SUPABASE_KEY'),
                'Authorization' => 'Bearer ' . env('SUPABASE_KEY'),
                'Content-Type' => $file->getMimeType(),
            ])->put(
                env('SUPABASE_URL') . "/storage/v1/object/{$bucket}/{$path}",
                file_get_contents($file)
            );

            if (!$response->successful()) {
                throw new \Exception('Gagal upload ke Supabase: ' . $response->body());
            }

            $publicUrl = env('SUPABASE_URL') . "/storage/v1/object/public/{$bucket}/{$path}";

            $foto = Foto::create([
                'album_id' => $validatedData['album_id'],
                'path_foto' => $publicUrl,
                'caption' => $validatedData['caption'] ?? null,
            ]);

            return response()->json([
                'data' => $foto,
                'message' => 'Foto berhasil diunggah ke Supabase dan disimpan',
                'code' => 201,
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error storing foto', ['message' => $e->getMessage()]);
            return response()->json([
                'message' => 'Gagal menyimpan foto: ' . $e->getMessage(),
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
        Log::info('Incoming Foto Update Request', ['id' => $id, 'data' => $request->all()]);

        $foto = Foto::findOrFail($id);

        $validatedData = $request->validate([
            'album_id' => 'required|exists:album,id',
            'file' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'caption' => 'nullable|string|max:255',
        ]);

        $dataToUpdate = [
            'album_id' => $validatedData['album_id'],
            'caption' => $validatedData['caption'] ?? null,
        ];

        if ($request->hasFile('file')) {
            $album = Album::findOrFail($validatedData['album_id']);
            $albumNameSlug = Str::slug($album->nama_album);
            $file = $request->file('file');
            $bucket = env('SUPABASE_BUCKET', 'images');
            $fileName = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = "images/album/{$albumNameSlug}/{$fileName}";

            $response = Http::withHeaders([
                'apikey' => env('SUPABASE_KEY'),
                'Authorization' => 'Bearer ' . env('SUPABASE_KEY'),
                'Content-Type' => $file->getMimeType(),
            ])->put(
                env('SUPABASE_URL') . "/storage/v1/object/{$bucket}/{$path}",
                file_get_contents($file)
            );

            if ($response->successful()) {
                $dataToUpdate['path_foto'] = env('SUPABASE_URL') . "/storage/v1/object/public/{$bucket}/{$path}";
            } else {
                Log::error('Failed to upload updated file to Supabase', ['response' => $response->body()]);
                return response()->json([
                    'message' => 'Gagal mengunggah file ke Supabase.',
                    'code' => 500,
                ], 500);
            }
        }

        $foto->update($dataToUpdate);

        return response()->json([
            'data' => $foto,
            'message' => 'Foto berhasil diperbarui',
            'code' => 200,
        ]);
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

        // Tidak bisa hapus file dari Supabase langsung tanpa SDK atau Signed URL
        // Alternatif: log atau tandai untuk dihapus manual atau melalui Supabase CLI / SDK

        $foto->delete();

        return response()->json([
            'message' => 'Foto berhasil dihapus (catatan: file di Supabase tidak dihapus otomatis)',
            'code' => 200,
        ]);
    }
}
