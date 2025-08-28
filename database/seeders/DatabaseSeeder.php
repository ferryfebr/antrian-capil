<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            LayananSeeder::class,
            LoketSeeder::class,
            // PengunjungSeeder::class, // Optional jika ingin data dummy
            // AntrianSeeder::class,    // Optional jika ingin data dummy
        ]);
    }
}