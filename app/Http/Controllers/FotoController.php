<?php

namespace App\Http\Controllers;
use App\Models\Foto;
use App\Models\Album;
use App\Models\Admin;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

            $path = $request->file('file')->store("images/album/{$albumNameSlug}", 'public');

            $foto = Foto::create([
                'album_id' => $validatedData['album_id'],
                'path_foto' => $path,
                'caption' => $validatedData['caption'] ?? null,
            ]);

            return response()->json([
                'data' => $foto,
                'message' => 'Foto berhasil diunggah dan disimpan',
                'code' => 201,
            ], 201);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Album not found during foto upload', ['album_id' => $request->album_id, 'message' => $e->getMessage()]);
            return response()->json([
                'message' => 'Album tidak ditemukan.',
                'code' => 404,
            ], 404);
        } catch (\Exception $e) {
            Log::error('Error storing foto', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Gagal mengunggah dan menyimpan foto: ' . $e->getMessage(),
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
        $foto = Foto::findOrFail($id);

        return response()->json([
            'data' => $foto,
            'message' => 'Data Foto Berhasil Ditampilkan',
            'code' => 200,
        ]);
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
            if ($foto->path_foto && Storage::disk('public')->exists($foto->path_foto)) {
                Storage::disk('public')->delete($foto->path_foto);
            }

            $album = Album::findOrFail($validatedData['album_id']);
            $albumNameSlug = Str::slug($album->nama_album);

            $path = $request->file('file')->store("images/album/{$albumNameSlug}", 'public');
            $dataToUpdate['path_foto'] = $path;
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
        $foto = Foto::where('id', $id)->first();

        if (!$foto) {
            return response()->json([
                'message' => 'Foto not found',
                'code' => 404,
            ], 404);
        }

        if ($foto->path_foto && Storage::disk('public')->exists($foto->path_foto)) {
            Storage::disk('public')->delete($foto->path_foto);
        }

        $foto->delete();

        return response()->json([
            'message' => 'Foto successfully deleted',
            'code' => 200,
        ]);
    }
}