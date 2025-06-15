<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Foto extends Model
{
    use HasFactory;

    protected $table = 'foto'; // Pastikan nama tabel ini sesuai
    protected $primaryKey = 'id'; // Tambahkan jika belum ada, biasanya defaultnya id
    protected $fillable = [
        'album_id',
        'path_foto', // Ini akan menyimpan path file foto
        'caption',
    ];

    // Relasi ke tabel admin (jika foto juga memiliki admin_id)
    // Jika tidak ada kolom admin_id di tabel 'foto', bagian ini bisa dihapus
    protected static function booted()
    {
        static::creating(function ($foto) {
            // Cek apakah admin_id belum diisi dan ambil admin pertama
            if (is_null($foto->admin_id)) {
                $admin = Admin::first(); // Pastikan model Admin ada dan terimport
                if ($admin) {
                    $foto->admin_id = $admin->id;
                } else {
                    // Opsional: Handle jika tidak ada admin, misalnya throw exception
                    // throw new \Exception("Tidak ada admin yang terdaftar untuk foto ini!");
                    // Atau set default ID jika ada admin dengan ID 1
                    $foto->admin_id = 1; // Contoh: asumsikan selalu ada admin dengan ID 1
                }
            }
        });
    }

    public function album()
    {
        return $this->belongsTo(Album::class);
    }
}