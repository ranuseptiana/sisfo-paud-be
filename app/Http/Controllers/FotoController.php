<?php

namespace App\Http\Controllers;
use App\Models\Foto;
use App\Models\Album;
use App\Models\Admin;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class FotoController extends Controller
{
    public function index()
    {
        $foto = Foto::all();
        return response ()->json([
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
        try {
            Log::info('Incoming request', ['data' => $request->all()]);

            $validatedData = $request->validate([
                '*.album_id' => 'required|exists:album,id',
                '*.path_foto' => 'required|string|max:255',
                '*.caption' => 'required|string|max:255',
            ]);

            Log::info('Validated data', ['data' => $validatedData]);

            $inserted = [];

            foreach ($validatedData as $data) {
                $foto = Foto::create($data);
                $inserted[] = $foto;
            }

            return response()->json([
                'data' => $inserted,
                'message' => 'Semua foto berhasil dibuat',
                'code' => 201,
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error saat tambah foto', ['message' => $e->getMessage()]);
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
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
    public function update(Request $request, $id) {

    }

    // Menghapus data foto
    public function destroy($id)
    {
        $foto = Foto::where('id', $id)->first();

        if (!$foto) {
            return response()->json([
                'message' => 'Foto not found',
                'code' => 404,
            ], 404);
        }

        $foto->delete();

        return response()->json([
            'message' => 'Foto successfully deleted',
            'code' => 200,
        ]);
    }
}
