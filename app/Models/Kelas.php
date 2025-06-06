<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';
    protected $fillable = ['nama_kelas','admin_id'];


    // Relasi ke tabel admin
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public $timestamps = false; // Menonaktifkan timestamps

    protected static function booted()
    {
        static::creating(function ($kelas) {
            // Cek apakah admin pertama ada
            $admin = Admin::first();
            if ($admin) {
                // Isi admin_id dengan admin pertama jika belum diisi
                if (is_null($kelas->admin_id)) {
                    $kelas->admin_id = $admin->id;
                }
            } else {
                // Jika tidak ada admin, beri pesan atau buat logika lain
                throw new \Exception("Tidak ada admin yang terdaftar!");
            }
        });
    }

    public function siswa()
    {
        return $this->hasMany(Siswa::class, 'kelas_id', 'id');
    }

    // Relasi ke RelasiKelas (menghubungkan ke Guru)
    public function relasiGuru()
    {
        return $this->hasMany(RelasiKelas::class, 'kelas_id', 'id');
    }

    // Ambil daftar guru melalui RelasiKelas
    public function guru()
    {
        return $this->hasManyThrough(
            Guru::class,
            RelasiKelas::class,
            'kelas_id',
            'id',
            'id',
            'guru_id'
        );
    }

    public function waliKelas()
    {
        return $this->hasOneThrough(
            Guru::class,
            RelasiKelas::class,
            'kelas_id',
            'id',
            'id',
            'guru_id'
        )->where('relasi_kelas.is_wali_kelas', 'true');
    }
}


