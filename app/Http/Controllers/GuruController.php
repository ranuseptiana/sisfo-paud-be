<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Admin;
use Illuminate\Http\Request;
use Carbon\Carbon;


class GuruController extends Controller
{
    public $timestamps = false;
    // Menyimpan data guru baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nip' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:guru,username',
            'password' => 'required|string|min:6',
            'nama_lengkap' => 'required|string|max:255',
            'gender' => 'required|string|max:255',
            'tgl_lahir' => 'required|date',
            'agama' => 'required|string|max:255',
            'alamat' => 'required|string',
            'no_telp' => 'required|string',
            'jabatan' => 'required|string',
            'jumlah_hari_mengajar' => 'required|integer',
            'tugas_mengajar' => 'required|string',
            'admin_id' => 'required|exists:admin,id', // Pastikan admin_id valid
        ]);

        $guru = Guru::create($validated);

        return response()->json([
            'data' => $guru,
            'message' => 'Guru successfully created',
            'code' => 201,
        ], 201);
    }

    // Menampilkan semua data guru
    public function index()
    {
        $guru = Guru::all();
        return response()->json([
            'data' => $guru,
            'message' => 'Success get guru data',
            'code' => 200,
        ]);
    }

    // Menghapus data guru
    public function destroy($id)
    {
        $guru = Guru::findOrFail($id);
        $guru->delete();

        return response()->json([
            'message' => 'Guru successfully deleted',
            'code' => 200,
        ]);
    }
}
