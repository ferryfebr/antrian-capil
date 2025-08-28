<?php

namespace Database\Seeders;

use App\Models\Layanan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LayananSeeder extends Seeder
{
    public function run(): void
    {
        $layananData = [
            [
                'nama_layanan' => 'Kartu Tanda Penduduk (KTP)',
                'kode_layanan' => 'KTP',
                'estimasi_durasi_layanan' => 30,
                'kapasitas_harian' => 40,
                'aktif' => true,
                'id_admin' => 2, // operator1
            ],
            [
                'nama_layanan' => 'Kartu Keluarga',
                'kode_layanan' => 'KK',
                'estimasi_durasi_layanan' => 25,
                'kapasitas_harian' => 35,
                'aktif' => true,
                'id_admin' => 2,
            ],
            [
                'nama_layanan' => 'Kartu Identitas Anak',
                'kode_layanan' => 'KIA',
                'estimasi_durasi_layanan' => 20,
                'kapasitas_harian' => 30,
                'aktif' => true,
                'id_admin' => 3, // operator2
            ],
            [
                'nama_layanan' => 'Akta Kelahiran',
                'kode_layanan' => 'AKTA',
                'estimasi_durasi_layanan' => 40,
                'kapasitas_harian' => 25,
                'aktif' => true,
                'id_admin' => 3,
            ],
            [
                'nama_layanan' => 'Pencatatan Perkawinan',
                'kode_layanan' => 'KAWIN',
                'estimasi_durasi_layanan' => 45,
                'kapasitas_harian' => 20,
                'aktif' => true,
                'id_admin' => 4, // supervisor
            ],
            [
                'nama_layanan' => 'Akta Kematian',
                'kode_layanan' => 'MATI',
                'estimasi_durasi_layanan' => 35,
                'kapasitas_harian' => 15,
                'aktif' => true,
                'id_admin' => 4,
            ],
            [
                'nama_layanan' => 'Surat Pindah',
                'kode_layanan' => 'PINDAH',
                'estimasi_durasi_layanan' => 30,
                'kapasitas_harian' => 20,
                'aktif' => true,
                'id_admin' => 2,
            ],
            [
                'nama_layanan' => 'Legalisir Dokumen',
                'kode_layanan' => 'LEGAL',
                'estimasi_durasi_layanan' => 15,
                'kapasitas_harian' => 50,
                'aktif' => true,
                'id_admin' => 3,
            ],
        ];

        foreach ($layananData as $layanan) {
            Layanan::create($layanan);
        }
    }
}