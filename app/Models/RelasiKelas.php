<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelasiKelas extends Model
{
    use HasFactory;

    protected $table = 'relasi_kelas';
    protected $fillable = [
        'kelas_id',
        'guru_id',
    ];

    public $timestamps = false;

    // public function siswa() {
    //     return $this->belongsTo(Siswa::class, 'siswa_id');
    // }

    public function kelas() {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function guru() {
        return $this->belongsTo(Guru::class, 'guru_id');
    }
}
