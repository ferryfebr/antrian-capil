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
        Schema::create('pengunjung', function (Blueprint $table) {
            $table->id('id_pengunjung');
            $table->string('nik', 16)->unique();
            $table->string('nama_pengunjung', 100);
            $table->string('no_hp', 15)->nullable();
            $table->timestamp('waktu_daftar')->useCurrent();
            
            $table->index('nik');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengunjung');
    }
};