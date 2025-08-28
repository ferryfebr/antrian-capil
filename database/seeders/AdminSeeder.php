<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        Admin::create([
            'username' => 'admin',
            'password' => Hash::make('password'),
            'nama_admin' => 'Super Administrator',
            'email' => 'admin@disdukcapil.go.id',
        ]);

        // Admin Operasional
        Admin::create([
            'username' => 'operator1',
            'password' => Hash::make('password123'),
            'nama_admin' => 'Operator Layanan 1',
            'email' => 'operator1@disdukcapil.go.id',
        ]);

        Admin::create([
            'username' => 'operator2',
            'password' => Hash::make('password123'),
            'nama_admin' => 'Operator Layanan 2',
            'email' => 'operator2@disdukcapil.go.id',
        ]);

        // Admin Supervisor
        Admin::create([
            'username' => 'supervisor',
            'password' => Hash::make('supervisor123'),
            'nama_admin' => 'Supervisor Pelayanan',
            'email' => 'supervisor@disdukcapil.go.id',
        ]);
    }
}
