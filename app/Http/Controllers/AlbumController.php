<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Album;
use App\Models\Admin; // Pastikan ini ada jika kamu menggunakan Admin
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; // Tambahkan ini
use Illuminate\Support\Facades\Log; // Tambahkan ini untuk logging

class AlbumController extends Controller
{
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Log request untuk debugging
        Log::info('Store Album Request', $request->all());

        $validated = $request->validate([
            'nama_album' => 'required|string|max:255',
            'deskripsi' => 'required|string|max:255',
            'tanggal_kegiatan' => 'nullable|date', // Tambahkan validasi
            'lokasi_kegiatan' => 'nullable|string|max:255', // Tambahkan validasi
            'photo_cover' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Generate nama folder dari nama album
        $folderName = Str::slug($request->nama_album);
        $path = $request->file('photo_cover')->store("images/album/{$folderName}", 'public');

        $data = $validated;
        $data['photo_cover'] = $path; // Simpan path ke database

        $album = Album::create($data);

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
    public function update(Request $request, $id)
    {
        // Log request untuk debugging
        Log::info('Update Album Request', ['id' => $id, 'data' => $request->all()]);

        $album = Album::findOrFail($id);

        $validated = $request->validate([
            'nama_album' => 'required|string|max:255',
            'deskripsi' => 'required|string|max:255',
            'tanggal_kegiatan' => 'nullable|date', // Tambahkan validasi
            'lokasi_kegiatan' => 'nullable|string|max:255', // Tambahkan validasi
            'photo_cover' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = $validated;

        // Proses upload file jika ada
        if ($request->hasFile('photo_cover')) {
            // Hapus foto lama jika ada
            if ($album->photo_cover) {
                // Pastikan path sudah benar relatif terhadap storage/app/public
                Storage::disk('public')->delete($album->photo_cover);
            }

            // Generate nama folder dari nama album yang baru (atau yang lama jika tidak berubah)
            // Penting: Jika nama_album berubah, folder juga akan berubah
            $folderName = Str::slug($request->nama_album); // Gunakan nama album dari request
            $path = $request->file('photo_cover')->store("images/album/{$folderName}", 'public');
            $data['photo_cover'] = $path;
        }

        $album->update($data);

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
    public function destroy($id)
    {
        $album = Album::where('id', $id)->first();

        if (!$album) {
            return response()->json([
                'message' => 'Album not found',
                'code' => 404,
            ], 404);
        }

        // Hapus foto cover jika ada
        if ($album->photo_cover) {
            Storage::disk('public')->delete($album->photo_cover);
        }

        $album->delete();

        return response()->json([
            'message' => 'Album successfully deleted', // Perbaiki pesan
            'code' => 200,
        ]);
    }
}