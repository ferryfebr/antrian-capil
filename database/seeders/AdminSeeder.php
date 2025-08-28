<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::create([
            'username' => 'admin',
            'password' => Hash::make('password'), // atau gunakan bcrypt('password')
            'nama_admin' => 'Administrator',
            'email' => 'admin@disdukcapil.go.id',
        ]);

        // Tambahkan admin lain jika diperlukan
        Admin::create([
            'username' => 'admin2',
            'password' => Hash::make('password123'),
            'nama_admin' => 'Admin Kedua',
            'email' => 'admin2@disdukcapil.go.id',
        ]);
    }
}