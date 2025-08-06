<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return response()->json(['error' => 'Username not found'], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Password mismatch'], 401);
        }

        // if (!$user || !Hash::check($request->password, $user->password)) {
        //     throw ValidationException::withMessages([
        //         'username' => ['The provided credentials are incorrect.'],
        //     ]);
        // }

        $token = $user->createToken('login-token')->plainTextToken;
        $expires_at = now()->addMinutes(2);

        return response()->json([
            'user' => $user,
            'token' => $token,
            'expires_at' => $expires_at,
            'user_type' => $user->user_type
        ]);
    }

    public function registerGuru(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users',
            'password' => 'required|min:6',
            'name' => 'required',
            'niy' => 'required|unique:guru',
            'alamat' => 'required',
            'gender' => 'required',
            'no_telp' => 'required',
            'tgl_lahir' => 'required|date',
            'agama' => 'required',
            'jabatan' => 'required',
            'jumlah_hari_mengajar' => 'required|integer',
            'tugas_mengajar' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $admin = $request->user(); // dari token login

        if (!$admin || $admin->user_type !== 'admin') {
            return response()->json(['message' => 'Hanya admin yang boleh mendaftarkan guru'], 403);
        }

        // Simpan data ke tabel guru
        $guru = Guru::create([
            'niy' => $request->niy,
            'nama_lengkap' => $request->name,
            'alamat' => $request->alamat,
            'gender' => $request->gender,
            'no_telp' => $request->no_telp,
            'tgl_lahir' => $request->tgl_lahir,
            'agama' => $request->agama,
            'jabatan' => $request->jabatan,
            'jumlah_hari_mengajar' => $request->jumlah_hari_mengajar,
            'tugas_mengajar' => $request->tugas_mengajar,
            'admin_id' => 1, //sementara ini admin_id 1
            // Jika admin_id tidak ada di request, bisa ambil dari admin yang login
            // 'admin_id' => $admin->admin_id ?? $admin->id,
        ]);

        // Simpan ke tabel user
        $user = User::create([
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'name' => $request->name,
            'user_type' => 'guru',
            'guru_id' => $guru->id,
        ]);

        return response()->json([
            'message' => 'Registrasi guru berhasil',
            'user' => $user,
            'guru' => $guru,
        ], 201);
    }

    public function registerSiswa(Request $request)
    {
        $request->validate([
            'username' => 'required|unique:users',
            'password' => 'required|min:6',
            'name' => 'required',
            'no_kk' => 'required|exists:orangtua,no_kk',
            'nik_siswa' => 'required|unique:siswa,nik_siswa|digits:16',
            'nisn' => 'required|unique:siswa,nisn|digits:10',
            'nipd' => 'required|unique:siswa,nipd|max:20',
            'tempat_lahir' => 'required',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'agama' => 'required',
            'alamat' => 'required',
            'anak_ke' => 'required|integer|min:1',
            'jumlah_saudara' => 'required|integer|min:0',
            'berat_badan' => 'required|integer|min:1',
            'tinggi_badan' => 'required|integer|min:1',
            'lingkar_kepala' => 'required|integer|min:1',
        ]);

        // Ambil admin pertama (atau bisa juga berdasarkan token login jika ada)
        $admin = Admin::first();
        if (!$admin) {
            return response()->json(['message' => 'Admin tidak ditemukan'], 500);
        }

        // Simpan data siswa
        $siswa = Siswa::create([
            'no_kk' => $request->no_kk,
            'nik_siswa' => $request->nik_siswa,
            'nisn' => $request->nisn,
            'nipd' => $request->nipd,
            'nama_siswa' => $request->name,
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jenis_kelamin' => $request->jenis_kelamin,
            'agama' => $request->agama,
            'alamat' => $request->alamat,
            'anak_ke' => $request->anak_ke,
            'jumlah_saudara' => $request->jumlah_saudara,
            'berat_badan' => $request->berat_badan,
            'tinggi_badan' => $request->tinggi_badan,
            'lingkar_kepala' => $request->lingkar_kepala,
            'admin_id' => 1, //sementara ini admin_id 1
            // Jika admin_id tidak ada di request, bisa ambil dari admin yang login
            // 'admin_id' => $admin->admin_id ?? $admin->id,
        ]);

        // Simpan user (login)
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'user_type' => 'siswa',
            'siswa_id' => $siswa->id,
        ]);

        return response()->json([
            'message' => 'Registrasi siswa berhasil',
            'user' => $user,
            'siswa' => $siswa
        ]);
    }

    public function refreshToken(Request $request)
    {
        $user = $request->user();
        $newToken = $user->createToken('login-token')->plainTextToken;

        return response()->json([
            'token' => $newToken,
            'expires_at' => now()->addMinutes(60)
        ]);
    }

    function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'logout success']);
    }
}
