<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    use HasFactory;

    protected $table = 'tahun_ajaran';

    protected $fillable = ['tahun', 'aktif'];

     public function admin()
     {
         return $this->belongsTo(Admin::class, 'admin_id');
     }

     protected static function booted()
    {
        static::creating(function ($tahun_ajaran) {
            $admin = Admin::first();
            if ($admin) {
                if (is_null($tahun_ajaran->admin_id)) {
                    $tahun_ajaran->admin_id = $admin->id;
                }
            } else {
                throw new \Exception("Tidak ada admin yang terdaftar!");
            }
        });
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
