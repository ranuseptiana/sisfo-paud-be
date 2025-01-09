<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Kelas;
use App\Models\Admin;

class KelasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Ambil admin pertama yang ada, atau buat admin baru jika tidak ada
        $admin = Admin::first(); // Bisa juga menggunakan Admin::create() jika ingin membuat admin baru

        // Jika tidak ada admin, buat admin baru
        if (!$admin) {
            $admin = Admin::create([
                'username' => 'admin',  // Sesuaikan dengan kolom username
                'password' => bcrypt('admin123'),  // Enkripsi password
            ]);
        }

        // Ambil admin_id dari admin yang ada
        $adminId = $admin->id;

        Kelas::create([
            'nama_kelas' => 'PG 1 Bintang',
            'admin_id' => $adminId,
        ]);

        Kelas::create([
            'nama_kelas' => 'PG Matahari',
            'admin_id' => $adminId,
        ]);

        Kelas::create([
            'nama_kelas' => 'A',
            'admin_id' => $adminId,
        ]);

        Kelas::create([
            'nama_kelas' => 'B',
            'admin_id' => $adminId,
        ]);

        Kelas::create([
            'nama_kelas' => 'A1',
            'admin_id' => $adminId,
        ]);

        Kelas::create([
            'nama_kelas' => 'A2',
            'admin_id' => $adminId,
        ]);

        Kelas::create([
            'nama_kelas' => 'B1',
            'admin_id' => $adminId,
        ]);

        Kelas::create([
            'nama_kelas' => 'B2',
            'admin_id' => $adminId,
        ]);
    }
}
