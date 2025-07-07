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

    protected $casts = [
        'nominal' => 'float',
    ];

    protected $appends = ['total_cicilan', 'sisa_pembayaran', 'status_cicilan'];

    protected static function booted()
{
    parent::booted();

    static::creating(function ($pembayaran) {
        $admin = Admin::first();
        if ($admin && is_null($pembayaran->admin_id)) {
            $pembayaran->admin_id = $admin->id;
        }

        if ($pembayaran->metode_pembayaran === 'full') {
            $pembayaran->status_pembayaran = 'Lunas';
        } else {
            $pembayaran->status_pembayaran = 'Belum Lunas';
        }
    });

    static::updating(function ($pembayaran) {
        if ($pembayaran->metode_pembayaran === 'full') {
            $pembayaran->status_pembayaran = 'Lunas';
        } else {
            $pembayaran->status_pembayaran = ($pembayaran->total_cicilan >= $pembayaran->nominal)
                ? 'Lunas'
                : 'Belum Lunas';
        }
    });
}

protected static function boot()
{
    parent::boot();

    static::updated(function ($pembayaran) {

        if ($pembayaran->isDirty('total_cicilan') || $pembayaran->isDirty('nominal')) {
            if ($pembayaran->total_cicilan >= $pembayaran->nominal) {
                $pembayaran->status_pembayaran = 'Lunas';
                $pembayaran->saveQuietly();
            }
        }
    });
}

public function updatePaymentStatus()
{
    if ($this->metode_pembayaran === 'full') {
        $this->status_pembayaran = 'Lunas';
    } else {
        $this->status_pembayaran = ($this->total_cicilan >= $this->nominal)
            ? 'Lunas'
            : 'Belum Lunas';
    }

    $this->save();
}

    public function getTotalCicilanAttribute()
    {
        if ($this->metode_pembayaran === 'full') {
            return (float) $this->nominal;
        }

        return (float) $this->cicilan()
        ->where('status_verifikasi', 'disetujui')
        ->sum('nominal_cicilan');
    }

    public function getStatusCicilanAttribute()
    {
        if ($this->metode_pembayaran === 'full') {
            return 'Lunas';
        }

        return ($this->total_cicilan >= $this->nominal) ? 'Lunas' : 'Belum Lunas';
    }

    public function getSisaPembayaranAttribute()
    {
        return max(0.0, (float) $this->nominal - (float) $this->total_cicilan);
    }

    public $timestamps = true;

    public function siswa() {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function cicilan()
    {
        return $this->hasMany(Cicilan::class);
    }


}
