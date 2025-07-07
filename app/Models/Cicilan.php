<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cicilan extends Model
{
    use HasFactory;

    protected $table = 'cicilan';
    protected $fillable = [
        'pembayaran_id',
        'nominal_cicilan',
        'tanggal_cicilan',
        'status_verifikasi',
        'tempat_tagihan',
        'keterangan',
        'admin_id'
    ];

    public $timestamps = true;

    public function pembayaran() {
        return $this->belongsTo(Pembayaran::class, 'pembayaran_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($cicilan) {
            $admin = Admin::first();
            if ($admin) {
                if (is_null($cicilan->admin_id)) {
                    $cicilan->admin_id = $admin->id;
                }
            } else {
                throw new \Exception("Tidak ada admin yang terdaftar!");
            }
        });

        static::saved(function ($cicilan) {
            if ($cicilan->pembayaran) {
                $cicilan->pembayaran->touch();
            }
        });

        static::deleted(function ($cicilan) {
            if ($cicilan->pembayaran) {
                $cicilan->pembayaran->touch();
            }
        });
    }
}
