<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Siswa;

class SiswaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $siswa = Siswa::all();
        return response()->json([
            'data' => $siswa,
            'message' => 'Data Siswa Berhasil Ditampilkan',
            'code' => 200,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
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
            'no_kk' => 'required|exists:orangtua,no_kk',
            'nik_siswa' => 'required|unique:siswa,nik_siswa',
            'nisn' => 'nullable|string|max:10',
            'nama_siswa' => 'required|string|max:255',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|string|max:255',
            'agama' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
            'anak_ke' => 'required|integer',
            'jumlah_saudara' => 'required|integer' ,
            'berat_badan' => 'required|integer',
            'tinggi_badan' => 'required|integer',
            'lingkar_kepala' => 'nullable|integer',
        ]);

        $data = $validated;

        $siswa = Siswa::create($data);

        return response()->json([
            'data' => $siswa,
            'message' => 'Data Siswa Berhasil Ditambahkan',
            'code' => 201,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $siswa = Siswa::findOrFail($id);

        return reponse()->json([
            'data' => $siswa,
            'message' => 'Data Siswa Berhasil Ditampilkan',
            'code' => 200,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        $validated = $request->validate([
            'no_kk' => 'required|exists:orangtua,no_kk',
            'nik_siswa' => 'required|unique:siswa,nik_siswa',
            'nisn' => 'nullable|string|max:10',
            'nama_siswa' => 'required|string|max:255',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date',
            'jenis_kelamin' => 'required|string|max:255',
            'agama' => 'required|string|max:255',
            'alamat' => 'required|string|max:255',
            'anak_ke' => 'required|integer',
            'jumlah_saudara' => 'required|integer' ,
            'berat_badan' => 'required|integer',
            'tinggi_badan' => 'required|integer',
            'lingkar_kepala' => 'required|integer',
        ]);

        $siswa = Siswa::findOrFail($id);
        $siswa->update($validated);

        return response()->json([
            'data' => $siswa,
            'message' => 'Data Siswa Berhasil Ditambahkan',
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
        $siswa = Siswa::findOrFail($id);
        $siswa->delete();
        return reponse()->json([
            'message' => 'Data Siswa Berhasil Dihapus',
            'code' => 200,
        ]);
    }
}
