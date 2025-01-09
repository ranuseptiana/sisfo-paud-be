<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admin extends Model
{
    use HasFactory;

    protected $table = 'admin'; // Nama tabel
    protected $fillable = ['email_admin', 'password_admin']; // Kolom yang bisa diisi

    // Relasi ke tabel kelas
    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'admin_id');
    }

    public $timestamps = false; // Menonaktifkan timestamps
}
