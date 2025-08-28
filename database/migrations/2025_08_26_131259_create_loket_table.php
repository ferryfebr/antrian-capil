<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loket', function (Blueprint $table) {
            $table->id('id_loket');
            $table->string('nama_loket', 50);
            $table->enum('status_loket', ['aktif', 'tidak_aktif'])->default('aktif');
            $table->text('deskripsi_loket')->nullable();
            $table->unsignedBigInteger('id_layanan')->nullable();
            $table->timestamps();
            
            $table->foreign('id_layanan')->references('id_layanan')->on('layanan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loket');
    }
};