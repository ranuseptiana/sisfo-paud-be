<?php

namespace App\Imports;

use App\Models\Siswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;


class SiswaImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    protected $maxRows = 100;
    protected $rowCount = 0;

    public function model(array $row)
{
    $kelasNama = trim(preg_replace('/\s+/', ' ', $row['kelas'] ?? ''));
    $kelasId = \App\Models\Kelas::where('nama_kelas', $kelasNama)->value('id');

    $tahunAjaranNama = trim($row['tahun_ajaran'] ?? '');
    $tahunAjaranId = \App\Models\TahunAjaran::where('tahun', $tahunAjaranNama)->value('id');

    $tahunLulusNama = trim($row['tahun_lulus'] ?? '');
    $tahunLulusId = \App\Models\TahunAjaran::where('tahun', $tahunLulusNama)->value('id');

    return new Siswa([
        'nik_siswa'       => $row['nik_siswa'] ?? null,
        'nipd'            => $row['nipd'] ?? null,
        'nisn'            => $row['nisn'] ?? null,
        'nama_siswa'      => $row['nama_siswa'] ?? null,
        'tempat_lahir'    => $row['tempat_lahir'] ?? null,
        'tanggal_lahir'   => isset($row['tanggal_lahir']) ? Carbon::parse($row['tanggal_lahir']) : null,
        'jenis_kelamin'   => $row['jenis_kelamin'] ?? null,
        'agama'           => $row['agama'] ?? null,
        'alamat'          => $row['alamat'] ?? null,
        'anak_ke'         => $row['anak_ke'] ?? null,
        'jumlah_saudara'  => $row['jumlah_saudara'] ?? null,
        'berat_badan'     => $row['berat_badan'] ?? null,
        'tinggi_badan'    => $row['tinggi_badan'] ?? null,
        'lingkar_kepala'  => $row['lingkar_kepala'] ?? null,
        'kelas_id'        => $kelasId,
        'status'          => $row['status'] ?? null,
        'tahun_ajaran_id' => $tahunAjaranId,
        'tahun_lulus_id'  => $tahunLulusId,
    ]);
}

public function rules(): array
{
    return [
        '*.nik_siswa'   => ['required', 'unique:siswa,nik_siswa'],
        '*.nama_siswa'  => ['required', 'string'],
        '*.kelas'       => ['required'],
        '*.tahun_ajaran'=> ['required'],
        '*.tahun_lulus' => ['nullable'],
    ];
}

}
