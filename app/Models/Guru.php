<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use HasFactory;

    public $timestamps = false; // Menonaktifkan penggunaan created_at dan updated_at

    protected $primaryKey = 'nip'; // Menentukan kolom nip sebagai primary key
    public $incrementing = false; // Karena nip bukan auto-increment

    protected $table = 'guru'; // Nama tabel
    protected $fillable = [
        'nip',
        'username',
        'password',
        'nama_lengkap',
        'gender',
        'agama',
        'alamat',
        'no_telp',
        'jabatan',
        'jumlah_hari_mengajar',
        'tugas_mengajar',
        'admin_id',
        'tgl_lahir' // Kolom tgl_lahir yang baru ditambahkan
    ]; // Kolom yang bisa diisi

    // Relasi ke tabel admin
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }
}
