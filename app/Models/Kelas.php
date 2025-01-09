<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas'; // Nama tabel
    protected $fillable = ['nama_kelas','admin_id']; // Kolom yang bisa diisi


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
}


