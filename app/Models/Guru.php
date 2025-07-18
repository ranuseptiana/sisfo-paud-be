<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use HasFactory;

    public $timestamps = false;

    // protected $primaryKey = 'nip';
    // public $incrementing = false;

    protected $table = 'guru';
    protected $fillable = [
        'nip',
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
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    protected static function booted()
    {
        static::creating(function ($guru) {
            $admin = Admin::first();
            if ($admin)
            {
                if (is_null($guru->admin_id)) {
                    $guru->admin_id = $admin->id;
                }
            } else {
                throw new \Exception("Tidak ada admin yang terdaftar!");
            }
        });
    }

    public function kelas()
    {
        return $this->belongsToMany(Kelas::class, 'relasi_kelas', 'guru_id', 'kelas_id');
    }

    public function user() {
        return $this->hasOne(User::class, 'guru_id');
    }

}
