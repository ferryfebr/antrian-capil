<?php

namespace Database\Seeders;

use App\Models\Loket;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LoketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $loketData = [
            [
                'nama_loket' => 'Loket 1',
                'status_loket' => 'aktif',
                'deskripsi_loket' => 'Loket untuk layanan KTP',
                'id_layanan' => 1, // KTP
            ],
            [
                'nama_loket' => 'Loket 2',
                'status_loket' => 'aktif',
                'deskripsi_loket' => 'Loket untuk layanan Kartu Keluarga',
                'id_layanan' => 2, // KK
            ],
            [
                'nama_loket' => 'Loket 3',
                'status_loket' => 'aktif',
                'deskripsi_loket' => 'Loket untuk layanan Kartu Identitas Anak',
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
                'status_loket' => 'tidak_aktif',
                'deskripsi_loket' => 'Loket untuk layanan Pencatatan Perkawinan',
                'id_layanan' => 5, // KAWIN
            ],
        ];

        foreach ($loketData as $loket) {
            Loket::create($loket);
        }
    }
}