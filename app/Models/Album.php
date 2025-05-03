<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use HasFactory;

    protected $table = 'album';
    protected $primaryKey = 'id';
    protected $fillable = [
        'nama_album',
        'deskripsi',
        'photo_cover'
    ];

    // Relasi ke tabel admin
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    protected static function booted()
    {
        static::creating(function ($album) {
            // Cek apakah admin pertama ada
            $admin = Admin::first();
            if ($admin) {
                // Isi admin_id dengan admin pertama jika belum diisi
                if (is_null($album->admin_id)) {
                    $album->admin_id = $admin->id;
                }
            } else {
                // Jika tidak ada admin, beri pesan atau buat logika lain
                throw new \Exception("Tidak ada admin yang terdaftar!");
            }
        });
    }

    public function foto()
    {
        return $this->hasMany(Foto::class);
    }
}
