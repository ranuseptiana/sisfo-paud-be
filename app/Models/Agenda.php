<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    use HasFactory;

    protected $table = 'agenda';
    protected $fillable = [
        'nama_kegiatan',
        'perkiraan_waktu',
        'kategori_kegiatan',
        'keterangan'
    ];

    // Relasi ke tabel admin
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public $timestamps = false; // Menonaktifkan timestamps

    protected static function booted()
    {
        static::creating(function ($agenda) {
            if (is_null($agenda->admin_id)) {
                $agenda->admin_id = 1;
            }
        });
    }
}
