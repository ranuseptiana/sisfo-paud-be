<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCicilanTable extends Migration
{
    public function up()
    {
        Schema::create('cicilan', function (Blueprint $table) {
            $table->id();
            $table->decimal('nominal_cicilan', 12, 2);
            $table->integer('sisa_cicilan');
            $table->date('tanggal_cicilan');
            $table->enum('status_verifikasi', ['pending', 'disetujui', 'ditolak']);
            $table->text('tempat_tagihan');
            $table->unsignedBigInteger('admin_id');
            $table->unsignedBigInteger('pembayaran_id');
            $table->timestamps();

            $table->foreign('admin_id')
                  ->references('id')->on('admin')
                  ->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('pembayaran_id')
                  ->references('id')->on('pembayaran')
                  ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cicilan');
    }
}
