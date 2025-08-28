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
        Schema::create('antrian', function (Blueprint $table) {
            $table->id('id_antrian');
            $table->string('nomor_antrian', 10);
            $table->timestamp('waktu_antrian')->useCurrent();
            $table->enum('status_antrian', ['menunggu', 'dipanggil', 'selesai', 'batal'])->default('menunggu');
            $table->timestamp('waktu_estimasi')->nullable();
            $table->timestamp('waktu_dipanggil')->nullable();
            $table->unsignedBigInteger('id_admin')->nullable();
            $table->unsignedBigInteger('id_pengunjung');
            $table->unsignedBigInteger('id_layanan');
            $table->timestamps();
            
            $table->foreign('id_admin')->references('id_admin')->on('admin');
            $table->foreign('id_pengunjung')->references('id_pengunjung')->on('pengunjung');
            $table->foreign('id_layanan')->references('id_layanan')->on('layanan');
            
            // Indexes untuk optimasi
            $table->index(['waktu_antrian']);
            $table->index(['status_antrian']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antrian');
    }
};