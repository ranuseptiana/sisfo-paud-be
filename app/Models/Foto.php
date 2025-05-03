<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Foto extends Model
{
    use HasFactory;

    protected $table = 'foto';

    protected $fillable = [
        'album_id',
        'path_foto',
        'caption',
    ];

    protected static function booted()
    {
        static::creating(function ($foto) {
            if (is_null($foto->admin_id)) {
                $foto->admin_id = 1;
            }
        });
    }

    public function album()
    {
        return $this->belongsTo(Album::class);
    }
}
