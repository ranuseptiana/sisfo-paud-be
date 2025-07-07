<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orangtua extends Model
{
    use HasFactory;

    protected $table = 'orangtua';
    protected $fillable = [
        'no_kk',
        'nik_ayah',
        'nama_ayah',
        'tahun_lahir_ayah',
        'pekerjaan_ayah',
        'pendidikan_ayah',
        'penghasilan_ayah',
        'nik_ibu',
        'nama_ibu',
        'tahun_lahir_ibu',
        'pekerjaan_ibu',
        'pendidikan_ibu',
        'penghasilan_ibu',
        'no_telp'
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public $timestamps = false;

    protected static function booted()
    {
        static::creating(function ($orangtua) {
            $admin = Admin::first();
            if ($admin) {
                if (is_null($orangtua->admin_id)) {
                    $orangtua->admin_id = $admin->id;
                }
            } else {
                throw new \Exception("Tidak ada admin yang terdaftar!");
            }
        });
    }

    public function siswa()
    {
        return $this->hasMany(Siswa::class, 'no_kk', 'no_kk');
    }
}
