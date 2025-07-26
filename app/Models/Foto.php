<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Foto extends Model
{
    use HasFactory;

    protected $table = 'foto';
    protected $primaryKey = 'id';
    protected $fillable = [
        'album_id',
        'path_foto',
        'caption'
    ];

    protected static function booted()
    {
        static::creating(function ($foto) {
            if (is_null($foto->admin_id)) {
                $admin = Admin::first();
                if ($admin) {
                    $foto->admin_id = $admin->id;
                } else {
                    $foto->admin_id = 1;
                }
            }
        });
    }

    public function album()
    {
        return $this->belongsTo(Album::class);
    }
}