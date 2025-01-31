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
        Schema::create('pembayaran_spp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('siswa_id');
            $table->foreign('siswa_id')->references('id')->on('siswa')->onDelete('cascade');
            $table->date('tanggal_pembayaran');
            $table->string('bukti_pembayaran');
            $table->string('status_pembayaran');
            $table->string('status_rapor');
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
        Schema::table('pembayaran_spp', function (Blueprint $table)
    {
        $table->dropForeign(['siswa_id']);
        $table->dropForeign(['admin_id']);
    });
        Schema::dropIfExists('pembayaran_spp');
    }
};
