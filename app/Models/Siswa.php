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

    // Relasi ke tabel admin
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public $timestamps = false; // Menonaktifkan timestamps

    protected static function booted()
    {
        static::creating(function ($siswa) {
            // Cek apakah admin pertama ada
            $admin = Admin::first();
            if ($admin) {
                // Isi admin_id dengan admin pertama jika belum diisi
                if (is_null($siswa->admin_id)) {
                    $siswa->admin_id = $admin->id;
                }
            } else {
                // Jika tidak ada admin, beri pesan atau buat logika lain
                throw new \Exception("Tidak ada admin yang terdaftar!");
            }
        });
    }

    protected $dates = ['tanggal_lahir']; // Pastikan field bertipe DATE di database

    public function getTanggalLahirAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }

    public function orangtua()
    {
        return $this->belongsTo(Orangtua::class, 'no_kk', 'no_kk');
    }

    public function pembayaranSPP()
    {
        return $this->hasMany(PembayaranSPP::class, 'siswa_id', 'id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_ajaran_id');
    }

}
