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
    Schema::create('guru', function (Blueprint $table) {
        $table->bigIncrements('id'); // Kolom id sebagai primary key auto-increment
        $table->string('nip')->unique(); // Kolom nip sebagai unique
        $table->string('username');
        $table->string('password');
        $table->string('nama_lengkap');
        $table->enum('gender', ['Laki-laki', 'Perempuan']);
        $table->date('tgl_lahir'); // Kolom tanggal lahir
        $table->string('agama');
        $table->text('alamat');
        $table->string('no_telp');
        $table->string('jabatan');
        $table->integer('jumlah_hari_mengajar');
        $table->string('tugas_mengajar');
        $table->unsignedBigInteger('admin_id'); // Relasi ke tabel admin

        $table->foreign('admin_id')->references('id')->on('admin'); // Menambahkan foreign key
        // Tidak menggunakan $table->timestamps() karena tidak ingin kolom created_at dan updated_at
    });
}


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('guru');
    }
};
