<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';
    protected $fillable = [
        'no_kk',
        'nik_siswa',
        'nipd',
        'nisn',
        'nama_siswa',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'alamat',
        'anak_ke',
        'jumlah_saudara',
        'berat_badan',
        'tinggi_badan',
        'lingkar_kepala',
        'kelas_id',
        'status',
        'tahun_ajaran_id'
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public $timestamps = false;

    protected static function booted()
    {
        static::creating(function ($siswa) {
            $admin = Admin::first();
            if ($admin) {
                if (is_null($siswa->admin_id)) {
                    $siswa->admin_id = $admin->id;
                }
            } else {
                throw new \Exception("Tidak ada admin yang terdaftar!");
            }
        });
    }

    protected $dates = ['tanggal_lahir'];

    public function getTanggalLahirAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }

    public function orangtua()
    {
        return $this->belongsTo(Orangtua::class, 'no_kk', 'no_kk');
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'siswa_id', 'id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'siswa_id');
    }

    public function getUserData()
    {
        return $this->load('user')->user;
    }
}