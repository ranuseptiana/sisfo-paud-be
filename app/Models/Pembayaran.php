<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';
    protected $fillable = [
        'siswa_id',
        'tanggal_pembayaran',
        'bukti_pembayaran',
        'status_pembayaran',
        'status_rapor',
        'nominal',
        'jenis_pembayaran',
        'metode_pembayaran',
        'status_atribut',
        'admin_id'
    ];

    public function getTotalCicilanAttribute()
    {
        return $this->cicilan->sum('nominal_cicilan');
    }

    public function getStatusCicilanAttribute()
    {
        return ($this->total_cicilan >= $this->nominal || $this->status_pembayaran === 'Lunas')
            ? 'Lunas'
            : 'Belum Lunas';
    }

    public function getSisaPembayaranAttribute()
    {
        return max(0, $this->nominal - $this->total_cicilan);
    }

    public $timestamps = false;

    public function siswa() {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    // Relasi ke tabel admin
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function cicilan()
    {
        return $this->hasMany(Cicilan::class);
    }

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($pembayaran) {
            $admin = Admin::first();
            if ($admin && is_null($pembayaran->admin_id)) {
                $pembayaran->admin_id = $admin->id;
            }

            if (isset($pembayaran->isCicilan) && !$pembayaran->isCicilan) {
                $pembayaran->status_pembayaran = 'Lunas';
            } else {
                $pembayaran->status_pembayaran = 'Belum Lunas';
            }
        });

        static::updating(function ($pembayaran) {
            $totalCicilan = $pembayaran->cicilan()
                ->where('status_verifikasi', 'disetujui')
                ->sum('nominal_cicilan');

            $pembayaran->status_pembayaran = ($totalCicilan >= $pembayaran->nominal)
                ? 'Lunas'
                : 'Belum Lunas';
        });
    }
}
