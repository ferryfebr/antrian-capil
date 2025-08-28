<?php

namespace Database\Seeders;

use App\Models\Layanan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LayananSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $layananData = [
            [
                'nama_layanan' => 'KTP',
                'kode_layanan' => 'KTP',
                'estimasi_durasi_layanan' => 30,
                'kapasitas_harian' => 40,
                'aktif' => true,
            ],
            [
                'nama_layanan' => 'Kartu Keluarga',
                'kode_layanan' => 'KK',
                'estimasi_durasi_layanan' => 25,
                'kapasitas_harian' => 35,
                'aktif' => true,
            ],
            [
                'nama_layanan' => 'Kartu Identitas Anak',
                'kode_layanan' => 'KIA',
                'estimasi_durasi_layanan' => 20,
                'kapasitas_harian' => 30,
                'aktif' => true,
            ],
            [
                'nama_layanan' => 'Akta Kelahiran',
                'kode_layanan' => 'AKTA',
                'estimasi_durasi_layanan' => 40,
                'kapasitas_harian' => 25,
                'aktif' => true,
            ],
            [
                'nama_layanan' => 'Pencatatan Perkawinan',
                'kode_layanan' => 'KAWIN',
                'estimasi_durasi_layanan' => 45,
                'kapasitas_harian' => 20,
                'aktif' => true,
            ],
            [
                'nama_layanan' => 'Akta Kematian',
                'kode_layanan' => 'MATI',
                'estimasi_durasi_layanan' => 35,
                'kapasitas_harian' => 15,
                'aktif' => true,
            ],
        ];

        foreach ($layananData as $layanan) {
            Layanan::create($layanan);
        }
    }
}