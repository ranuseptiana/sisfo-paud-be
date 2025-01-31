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
        Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->string('no_kk'); // Menambahkan kolom 'no kk'
            $table->foreign('no_kk')->references('no_kk')->on('orangtua')->onDelete('cascade'); // Foreign key
            $table->string('nik_siswa', 16)->unique();
            $table->string('nisn', 10)->unique();
            $table->string('nama_siswa');
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->string('agama');
            $table->string('alamat');
            $table->integer('anak_ke');
            $table->integer('jumlah_saudara');
            $table->integer('berat_badan');
            $table->integer('tinggi_badan');
            $table->integer('lingkar_kepala');
            $table->unsignedBigInteger('admin_id'); // Menambahkan kolom 'admin_id'
            $table->foreign('admin_id')->references('id')->on('admin')->onDelete('cascade'); // Foreign key
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
        Schema::table('siswa', function (Blueprint $table) {
            $table->dropForeign(['no_kk']);
            $table->dropForeign(['admin_id']);
        });

        Schema::dropIfExists('siswa'); // Harus terakhir
    }
};
