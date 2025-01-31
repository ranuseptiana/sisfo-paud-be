<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembayaranSpp extends Model
{
    use HasFactory;

    protected $table = 'pembayaran_spp';
    protected $fillable = [
        'siswa_id',
        'tanggal_pembayaran',
        'bukti_pembayaran',
        'status_pembayaran',
        'status_rapor'
    ];

    public $timestamps = false;

    public function siswa() {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    // Relasi ke tabel admin
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    protected static function booted()
    {
        static::creating(function ($orangtua) {
            // Cek apakah admin pertama ada
            $admin = Admin::first();
            if ($admin) {
                // Isi admin_id dengan admin pertama jika belum diisi
                if (is_null($orangtua->admin_id)) {
                    $orangtua->admin_id = $admin->id;
                }
            } else {
                // Jika tidak ada admin, beri pesan atau buat logika lain
                throw new \Exception("Tidak ada admin yang terdaftar!");
            }
        });
    }
}
