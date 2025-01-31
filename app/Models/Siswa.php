<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';
    protected $fillable = [
        'no_kk',
        'nik_siswa',
        'nisn',
        'nama_siswa',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'alamat',
        'anak_ke',
        'jumlah_saudara',
        'berat_badan',
        'tinggi_badan',
        'lingkar_kepala',
    ];

    // Relasi ke tabel admin
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public $timestamps = false; // Menonaktifkan timestamps

    protected static function booted()
    {
        static::creating(function ($siswa) {
            // Cek apakah admin pertama ada
            $admin = Admin::first();
            if ($admin) {
                // Isi admin_id dengan admin pertama jika belum diisi
                if (is_null($siswa->admin_id)) {
                    $siswa->admin_id = $admin->id;
                }
            } else {
                // Jika tidak ada admin, beri pesan atau buat logika lain
                throw new \Exception("Tidak ada admin yang terdaftar!");
            }
        });
    }

    public function orangtua()
    {
        return $this->belongsTo(Orangtua::class, 'no_kk', 'no_kk');
    }

}
