<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Album;
use App\Models\Admin;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AlbumController extends Controller
{
    private function uploadToSupabase($file, $folderName)
    {
        $bucket = 'images';
        $fileName = $file->hashName();
        $path = "album/{$folderName}/{$fileName}";

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

    public function index()
    {
        $album = Album::all();
        return response()->json([
            'data' => $album,
            'message' => 'Data Album Berhasil Ditampilkan',
            'code' => 200,
        ]);
    }

    public function indexWithFoto()
    {
        $albums = Album::with('foto')->get();

        return response()->json([
            'status' => 'success',
            'data' => $albums
        ]);
    }

    public function store(Request $request)
    {
        Log::info('Store Album Request', $request->all());

        $validated = $request->validate([
            'nama_album' => 'required|string|max:255',
            'deskripsi' => 'required|string|max:255',
            'tanggal_kegiatan' => 'nullable|date',
            'lokasi_kegiatan' => 'nullable|string|max:255',
            'photo_cover' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $folderName = Str::slug($request->nama_album);

        $coverUrl = $this->uploadToSupabase($request->file('photo_cover'), $folderName);

        if (!$coverUrl) {
            return response()->json([
                'message' => 'Upload ke Supabase gagal',
                'code' => 500,
            ], 500);
        }

        $data = $validated;
        $data['photo_cover'] = $coverUrl;

        $album = Album::create($data);

        return response()->json([
            'data' => $album,
            'message' => 'Album successfully created',
            'code' => 201,
        ], 201);
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
        Log::info('Update Album Request', ['id' => $id, 'data' => $request->all()]);

        $album = Album::findOrFail($id);

        $validated = $request->validate([
            'nama_album' => 'required|string|max:255',
            'deskripsi' => 'required|string|max:255',
            'tanggal_kegiatan' => 'nullable|date',
            'lokasi_kegiatan' => 'nullable|string|max:255',
            'photo_cover' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $validated;

        if ($request->hasFile('photo_cover')) {
            $folderName = Str::slug($request->nama_album);

            $coverUrl = $this->uploadToSupabase($request->file('photo_cover'), $folderName);

            if (!$coverUrl) {
                return response()->json([
                    'message' => 'Upload ke Supabase gagal saat update',
                    'code' => 500,
                ], 500);
            }

            $data['photo_cover'] = $coverUrl;
        }

        $album->update($data);

        return response()->json([
            'data' => $album,
            'message' => 'Data Album Berhasil Diperbarui',
            'code' => 200,
        ]);
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