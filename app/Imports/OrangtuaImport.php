<?php

namespace App\Imports;

use App\Models\Orangtua;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class OrangtuaImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new Orangtua([
            'no_kk' => $row['no_kk'],
            'nik_ayah' => $row['nik_ayah'],
            'nama_ayah' => $row['nama_ayah'],
            'tahun_lahir_ayah' => $row['tahun_lahir_ayah'],
            'pekerjaan_ayah' => $row['pekerjaan_ayah'],
            'pendidikan_ayah' => $row['pendidikan_ayah'],
            'penghasilan_ayah' => $row['penghasilan_ayah'],
            'nik_ibu' => $row['nik_ibu'],
            'nama_ibu' => $row['nama_ibu'],
            'tahun_lahir_ibu' => $row['tahun_lahir_ibu'],
            'pekerjaan_ibu' => $row['pekerjaan_ibu'],
            'pendidikan_ibu' => $row['pendidikan_ibu'],
            'penghasilan_ibu' => $row['penghasilan_ibu'],
            'no_telp' => $row['no_telp'],
        ]);
    }
}
