<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class updateUserSiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $siswa = DB::table('siswa')->get();

    foreach ($siswa as $index => $s) {
        DB::table('users')
        ->where('user_type', 'siswa')
        ->where('siswa_id', $s->id)
        ->update(['siswa_id' => $s->id]);
    }
    }
}
