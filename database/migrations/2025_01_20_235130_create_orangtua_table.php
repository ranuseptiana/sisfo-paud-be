<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tabel_orangtua', function (Blueprint $table) {
            $table->string('nik_ayah')->default('Tidak diketahui')->nullable()->change();
            $table->string('nama_ayah')->default('Tidak diketahui')->nullable()->change();
            $table->string('nik_ibu')->default('Tidak diketahui')->nullable()->change();
            $table->string('nama_ibu')->default('Tidak diketahui')->nullable()->change();
            $table->string('no_telp')->default('Tidak diketahui')->nullable()->change();
        });

        Schema::create('orangtua', function (Blueprint $table) {
            $table->id();
            $table->string('no_kk', 16)->unique(); // Kolom kk sebagai unique
            $table->string('nik_ayah', 16)->unique(); // Kolom nik sebagai unique
            $table->string('nama_ayah');
            $table->integer('tahun_lahir_ayah');
            $table->string('pekerjaan_ayah');
            $table->string('pendidikan_ayah');
            $table->string('penghasilan_ayah');
            $table->string('nik_ibu', 16)->unique(); // Kolom nik sebagai unique
            $table->string('nama_ibu');
            $table->integer('tahun_lahir_ibu');
            $table->string('pekerjaan_ibu');
            $table->string('pendidikan_ibu');
            $table->string('penghasilan_ibu');
            $table->string('no_telp');
        });
    }

    // Menonaktifkan timestamps
    public $timestamps = false;

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orangtua');
    }
};
