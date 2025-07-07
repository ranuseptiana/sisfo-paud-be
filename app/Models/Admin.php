<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $table = 'admin';
    protected $fillable = ['username', 'password'];

    protected $hidden = [
        'password',
    ];

    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'admin_id');
    }

    public $timestamps = false;
}
