<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use HasFactory;

    public $timestamps = false; // Menonaktifkan penggunaan created_at dan updated_at

    // protected $primaryKey = 'nip'; // Menentukan kolom nip sebagai primary key
    // public $incrementing = false; // Karena nip bukan auto-increment

    protected $table = 'guru'; // Nama tabel
    protected $fillable = [
        'nip',
        'username',
        'password',
        'nama_lengkap',
        'gender',
        'agama',
        'alamat',
        'no_telp',
        'jabatan',
        'jumlah_hari_mengajar',
        'tugas_mengajar',
        'admin_id',
        'tgl_lahir',
        'tempat_lahir',
    ]; // Kolom yang bisa diisi

    // Relasi ke tabel admin
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    protected static function booted()
    {
        static::creating(function ($guru) {
            // Cek apakah admin pertama ada
            $admin = Admin::first();
            if ($admin) {
                // Isi admin_id dengan admin pertama jika belum diisi
                if (is_null($guru->admin_id)) {
                    $guru->admin_id = $admin->id;
                }
            } else {
                // Jika tidak ada admin, beri pesan atau buat logika lain
                throw new \Exception("Tidak ada admin yang terdaftar!");
            }
        });
    }
}
