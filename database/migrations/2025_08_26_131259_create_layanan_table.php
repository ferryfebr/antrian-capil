<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('layanan', function (Blueprint $table) {
            // PERBAIKAN: Set default value untuk kolom aktif
            $table->boolean('aktif')->default(true)->change();
        });

        // Update existing records yang mungkin null
        DB::table('layanan')->whereNull('aktif')->update(['aktif' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('layanan', function (Blueprint $table) {
            $table->boolean('aktif')->default(false)->change();
        });
    }
};