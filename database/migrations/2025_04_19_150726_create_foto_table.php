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
        Schema::create('foto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained('album')->onDelete('cascade');
            $table->string('path_foto'); // atau 'path' sesuai kebutuhan
            $table->string('caption')->nullable();
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
        Schema::table('foto', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
        });
        Schema::dropIfExists('foto');
    }
};
