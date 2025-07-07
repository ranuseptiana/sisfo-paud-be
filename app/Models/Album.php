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
        'tanggal_kegiatan',
        'lokasi_kegiatan',
        'photo_cover'
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    protected static function booted()
    {
        static::creating(function ($album) {
            $admin = Admin::first();
            if ($admin) {
                if (is_null($album->admin_id)) {
                    $album->admin_id = $admin->id;
                }
            } else {
                throw new \Exception("Tidak ada admin yang terdaftar!");
            }
        });
    }

    public function foto()
    {
        return $this->hasMany(Foto::class);
    }
}
