<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orangtua;

class OrangtuaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orangtua = Orangtua::all();
        return response()->json([
            'data' => $orangtua,
            'message' => 'Success get orangtua data',
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
            'no_kk' => 'required|unique:orangtua,no_kk',
            'nik_ayah' => 'nullable|unique:orangtua,nik_ayah',
            'nama_ayah' => 'nullable|string|max:255',
            'tahun_lahir_ayah' => 'required|integer',
            'pekerjaan_ayah' => 'required|string|max:255',
            'pendidikan_ayah' => 'required|string|max:255',
            'penghasilan_ayah' => 'required|string|max:500',
            'nik_ibu' => 'nullable|unique:orangtua,nik_ibu',
            'nama_ibu' => 'nullable|string|max:255',
            'tahun_lahir_ibu' => 'required|integer',
            'pekerjaan_ibu' => 'required|string|max:255',
            'pendidikan_ibu' => 'required|string|max:255',
            'penghasilan_ibu' => 'required|string|max:255',
            'no_telp' => 'nullable|integer',
        ]);

        // Tentukan nilai default jika kolom tertentu null
        $data = $validated;

        // Simpan data ke database
        $orangtua = OrangTua::create($data);

        // Kembalikan response sukses
        return response()->json([
            'data' => $orangtua,
            'message' => 'Orangtua successfully created',
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
        $orangtua = Orangtua::findOrFail($id);

        return response()->json([
            'data' => $orangtua,
            'message' => 'Success get specific orangtua data',
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
            'no_kk' => 'required|unique:orangtua,no_kk,' . $id,
            'nik_ayah' => 'nullable|unique:orangtua,nik_ayah,' . $id,
            'nama_ayah' => 'nullable|string|max:255',
            'tahun_lahir_ayah' => 'required|integer',
            'pekerjaan_ayah' => 'required|string|max:255',
            'pendidikan_ayah' => 'required|string|max:255',
            'penghasilan_ayah' => 'required|string|max:500',
            'nik_ibu' => 'nullable|unique:orangtua,nik_ibu,' . $id,
            'nama_ibu' => 'nullable|string|max:255',
            'tahun_lahir_ibu' => 'required|integer',
            'pekerjaan_ibu' => 'required|string|max:255',
            'pendidikan_ibu' => 'required|string|max:255',
            'penghasilan_ibu' => 'required|string|max:255',
            'no_telp' => 'nullable|string|max:255',
        ]);

        $orangtua = Orangtua::findOrFail($id);
        $orangtua->update($validated);

        return response()->json([
            'data' => $orangtua,
            'message' => 'Orangtua successfully updated',
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
        $orangtua = Orangtua::findOrFail($id);
        $orangtua->delete();
        return response()->json([
            'message' => 'Orangtua successfully deleted',
            'code' => 200,
        ]);
    }
}
