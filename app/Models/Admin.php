<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $table = 'admin'; // Nama tabel
    protected $fillable = ['username', 'password']; // Kolom yang bisa diisi

    protected $hidden = [
        'password', // Pastikan ini benar
    ];

    // Relasi ke tabel kelas
    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'admin_id');
    }

    public $timestamps = false; // Menonaktifkan timestamps
}
