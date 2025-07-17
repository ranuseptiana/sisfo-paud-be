<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Orangtua;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\OrangtuaImport;

class OrangtuaController extends Controller
{
    public function getByNoKK($no_kk)
    {
        $orangtua = Orangtua::where('no_kk', $no_kk)->firstOrFail();

        return response()->json([
            'data' => $orangtua,
            'message' => 'Data orangtua ditemukan',
            'code' => 200
        ]);
    }

    public function importOrangtua(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv|max:2048',
            ]);

            Excel::import(new OrangtuaImport, $request->file('file'));

            return response()->json([
                'message' => 'Data orangtua berhasil diimpor'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengimpor data orangtua: ' . $e->getMessage()
            ], 500);
        }
    }

    public function exportOrangtua(Request $request)
    {
        try {
            $selectedColumns = $request->input('columns', []);

            if (!is_array($selectedColumns) || empty($selectedColumns)) {
                $selectedColumns = $allowedColumns;
            }

            $allowedColumns = [
                'no_kk', 'nik_ayah', 'nama_ayah', 'pekerjaan_ayah', 'penghasilan_ayah',
                'nik_ibu', 'nama_ibu', 'pekerjaan_ibu', 'penghasilan_ibu',
                'no_telp', 'pendidikan_ayah', 'pendidikan_ibu', 'tahun_lahir_ayah',
                'tahun_lahir_ibu'
            ];

            $filteredColumns = array_filter($selectedColumns, function($col) use ($allowedColumns) {
                return in_array($col, $allowedColumns);
            });

            if (empty($filteredColumns)) {
                $filteredColumns = $allowedColumns;
            }

            if (!in_array('no_kk', $filteredColumns)) {
                array_unshift($filteredColumns, 'no_kk');
            }

            $data = DB::table('orangtua')
                ->select($filteredColumns)
                ->get()
                ->map(function($item) {
                    $itemArray = (array)$item;
                    if (isset($itemArray['tahun_lahir_ayah'])) {
                        $itemArray['tahun_lahir_ayah'] = (string)$itemArray['tahun_lahir_ayah'];
                    }
                    if (isset($itemArray['tahun_lahir_ibu'])) {
                        $itemArray['tahun_lahir_ibu'] = (string)$itemArray['tahun_lahir_ibu'];
                    }
                    return $itemArray;
                });

            return response()->json([
                'success' => true,
                'data' => $data,
                'message' => 'Data berhasil diambil'
            ]);

        } catch (\Exception $e) {
            \Log::error('Export error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengekspor data: ' . $e->getMessage()
            ], 500);
        }
    }

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
            'no_kk' => 'nullable|unique:orangtua,no_kk',
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
            'no_telp' => 'nullable|string|max:16',
        ]);

        $data = $validated;
        $orangtua = OrangTua::create($data);

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
            'no_kk' => 'nullable|unique:orangtua,no_kk,' . $id,
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
            'no_telp' => 'nullable|string|max:16',
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
