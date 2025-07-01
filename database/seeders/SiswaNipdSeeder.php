<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SiswaNipdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

    $file = database_path('seeders/data/siswaNipd.csv');
    $csvData = array_map('str_getcsv', file($file));
    $header = array_shift($csvData);

    foreach ($csvData as $row) {
        $data = array_combine($header, $row);

        Siswa::where('id', $data['id'])
            ->update(['nipd' => $data['nipd']]);
    }
    }
}
