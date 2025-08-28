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
        Schema::create('layanan', function (Blueprint $table) {
            $table->id('id_layanan');
            $table->string('nama_layanan', 100);
            $table->string('kode_layanan', 10);
            $table->integer('estimasi_durasi_layanan')->default(30)->comment('dalam menit');
            $table->integer('kapasitas_harian')->default(50);
            $table->boolean('aktif')->default(true);
            $table->unsignedBigInteger('id_admin')->nullable();
            $table->timestamps();
            
            $table->foreign('id_admin')->references('id_admin')->on('admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('layanan');
    }
};