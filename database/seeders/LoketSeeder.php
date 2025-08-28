<?php

namespace Database\Seeders;

use App\Models\Loket;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LoketSeeder extends Seeder
{
    public function run(): void
    {
        $loketData = [
            [
                'nama_loket' => 'Loket 1',
                'status_loket' => 'aktif',
                'deskripsi_loket' => 'Loket khusus untuk layanan KTP dan KK',
                'id_layanan' => 1, // KTP
            ],
            [
                'nama_loket' => 'Loket 2', 
                'status_loket' => 'aktif',
                'deskripsi_loket' => 'Loket khusus untuk layanan Kartu Keluarga',
                'id_layanan' => 2, // KK
            ],
            [
                'nama_loket' => 'Loket 3',
                'status_loket' => 'aktif', 
                'deskripsi_loket' => 'Loket khusus untuk layanan KIA dan Akta',
                'id_layanan' => 3, // KIA
            ],
            [
                'nama_loket' => 'Loket 4',
                'status_loket' => 'aktif',
                'deskripsi_loket' => 'Loket untuk layanan Akta Kelahiran',
                'id_layanan' => 4, // AKTA
            ],
            [
                'nama_loket' => 'Loket 5',
                'status_loket' => 'aktif',
                'deskripsi_loket' => 'Loket VIP untuk layanan Perkawinan',
                'id_layanan' => 5, // KAWIN
            ],
            [
                'nama_loket' => 'Loket 6',
                'status_loket' => 'tidak_aktif',
                'deskripsi_loket' => 'Loket cadangan untuk masa sibuk',
                'id_layanan' => null, // Umum
            ],
            [
                'nama_loket' => 'Loket Informasi',
                'status_loket' => 'aktif',
                'deskripsi_loket' => 'Loket khusus untuk informasi dan konsultasi',
                'id_layanan' => null, // Umum
            ],
            [
                'nama_loket' => 'Loket Express',
                'status_loket' => 'aktif', 
                'deskripsi_loket' => 'Loket untuk layanan cepat seperti legalisir',
                'id_layanan' => 8, // LEGAL
            ],
        ];

        foreach ($loketData as $loket) {
            Loket::create($loket);
        }
    }
}