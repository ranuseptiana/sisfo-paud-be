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

class SiswaImport implements ToModel, WithHeadingRow, WithValidation
{
    protected $maxRows = 100;
    protected $rowCount = 0;

    public function model(array $row)
    {
        $this->rowCount++;

        if ($this->rowCount > $this->maxRows) {
            throw new \Exception('Maksimal 100 baris yang diperbolehkan.');
        }

        return new Siswa([
            'no_kk' => (string) $row['no_kk'] ?? null,
            'nik_siswa' => $row['nik_siswa'] ?? null,
            'nipd' => $row['nipd'] ?? null,
            'nisn' => $row['nisn'] ?? null,
            'nama_siswa' => $row['nama_siswa'] ?? null,
            'tempat_lahir' => $row['tempat_lahir'] ?? null,
            'tanggal_lahir' => isset($row['tanggal_lahir']) ? Carbon::parse($row['tanggal_lahir']) : null,
            'jenis_kelamin' => match (strtolower($row['jenis_kelamin'] ?? '')) {
                    'l', 'laki-laki' => 'Laki-laki',
                    'p', 'perempuan' => 'Perempuan',
                    default => null,
            },
            'agama' => $row['agama'] ?? null,
            'alamat' => $row['alamat'] ?? null,
            'anak_ke' => $row['anak_ke'] ?? null,
            'jumlah_saudara' => $row['jumlah_saudara'] ?? null,
            'berat_badan' => $row['berat_badan'] ?? null,
            'tinggi_badan' => $row['tinggi_badan'] ?? null,
            'lingkar_kepala' => $row['lingkar_kepala'] ?? null,
            'kelas_id' => $row['kelas_id'] ?? null,
            'status' => $row['status'] ?? null,
            'tahun_ajaran_id' => $row['tahun_ajaran_id'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            '*.nik_siswa' => ['required', 'unique:siswa,nik_siswa'],
            '*.nama_siswa' => ['required', 'string'],
        ];
    }
}
