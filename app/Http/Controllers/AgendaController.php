<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Agenda;
use App\Models\Admin;

class AgendaController extends Controller
{
    public $timestamps = false;

    public function index()
    {
        $agenda = Agenda::all();
        return response ()->json([
            'data' => $agenda,
            'message' => 'Data Agenda Berhasil Ditampilkan',
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
            'nama_kegiatan' => 'required|string|max:255',
            'kategori_kegiatan' => 'required|string|max:255',
            'perkiraan_waktu' => 'required|string|max:255',
            'keterangan' => 'required|string|max:255'
        ]);

        $agenda = Agenda::create($validated);

        return response()->json([
            'data' => $agenda,
            'message' => 'Agenda successfully created',
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
        $agenda = Agenda::findOrFail($id);

        return response()->json([
            'data' => $agenda,
            'message' => 'Success get agenda data',
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
    public function update(Request $request, $id) {

        $validated = $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'kategori_kegiatan' => 'required|string|max:255',
            'perkiraan_waktu' => 'required|string|max:255',
            'keterangan' => 'required|string|max:255',
        ]);

        $agenda = Agenda::findOrFail($id);

        $agenda->update($validated);

        return response()->json([
            'data' => $agenda,
            'message' => 'Data Agenda Berhasil Diperbarui',
            'code' => 200,
        ]);
        }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // Menghapus data agenda
    public function destroy($id)
    {
        $agenda = Agenda::where('id', $id)->first();

        if (!$agenda) {
            return response()->json([
                'message' => 'Agenda not found',
                'code' => 404,
            ], 404);
        }

        $agenda->delete();

        return response()->json([
            'message' => 'Agenda successfully deleted',
            'code' => 200,
        ]);
    }
}
