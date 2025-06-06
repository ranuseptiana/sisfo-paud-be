<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;

class ImportUsersSeeder extends Seeder
{
    /**
     * Jalankan database seeders untuk mengimpor CSV.
     *
     * @return void
     */
    public function run()
    {
        // Tentukan path file CSV
        Excel::import(new UsersImport, public_path('user.csv')); // Sesuaikan dengan lokasi file CSV
    }
}
