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
        Schema::create('album', function (Blueprint $table) {
            $table->id();
            $table->string('nama_album');
            $table->string('deskripsi')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('admin_id'); // Menambahkan kolom 'admin_id'
            $table->foreign('admin_id')->references('id')->on('admin')->onDelete('cascade'); // Foreign key
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('album', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
        });
        Schema::dropIfExists('album');
    }
};
