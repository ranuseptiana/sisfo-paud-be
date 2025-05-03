<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Album;
use App\Models\Admin;

class AlbumController extends Controller
{
    public function index()
    {
        $album = Album::all();
        return response ()->json([
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_album' => 'required|string|max:255',
            'deskripsi' => 'required|string|max:255',
            'photo_cover' => 'required|string|max:255',
        ]);

        // Tentukan nilai default jika kolom tertentu null
        $data = $validated;

        // Simpan data ke database
        $album = Album::create($data);

        // Kembalikan response sukses
        return response()->json([
            'data' => $album,
            'message' => 'Album successfully created',
            'code' => 201,
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $album = Album::findOrFail($id);

        return response()->json([
            'data' => $album,
            'message' => 'Data Album Berhasil Ditampilkan',
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

        $validated = $request->validate([
            'nama_album' => 'required|string|max:255',
            'deskripsi' => 'required|string|max:255',
            'photo_cover' => 'required|string|max:255',
        ]);

        $album = Album::findOrFail($id);

        $album->update($validated);

        return response()->json([
            'data' => $album,
            'message' => 'Data Album Berhasil Diperbarui',
            'code' => 200,
        ]);
        }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // Menghapus data album
    public function destroy($id)
    {
        $album = Album::where('id', $id)->first();

        if (!$album) {
            return response()->json([
                'message' => 'Album not found',
                'code' => 404,
            ], 404);
        }

        $album->delete();

        return response()->json([
            'message' => 'Guru successfully deleted',
            'code' => 200,
        ]);
    }
}
