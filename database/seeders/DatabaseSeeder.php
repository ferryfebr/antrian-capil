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
        // 1. Admin Seeder
        $this->call(AdminSeeder::class);
        
        // 2. Layanan Seeder
        $this->call(LayananSeeder::class);
        
        // 3. Loket Seeder  
        $this->call(LoketSeeder::class);
        
        // 4. Sample Data untuk Development/Testing
        if (app()->environment(['local', 'development'])) {
            $this->createSampleData();
        }
    }

    /**
     * Create sample data for development
     */
    private function createSampleData()
    {
        // Create sample pengunjung
        $pengunjungs = [
            [
                'nik' => '3471010101010001',
                'nama_pengunjung' => 'Budi Santoso',
                'no_hp' => '081234567001',
                'waktu_daftar' => Carbon::now()->subDays(5),
            ],
            [
                'nik' => '3471010202020002',
                'nama_pengunjung' => 'Siti Rahayu',
                'no_hp' => '081234567002',
                'waktu_daftar' => Carbon::now()->subDays(3),
            ],
            [
                'nik' => '3471010303030003',
                'nama_pengunjung' => 'Ahmad Wijaya',
                'no_hp' => '081234567003',
                'waktu_daftar' => Carbon::now()->subDays(2),
            ],
            [
                'nik' => '3471010404040004',
                'nama_pengunjung' => 'Indira Sari',
                'no_hp' => null,
                'waktu_daftar' => Carbon::now()->subDay(),
            ],
            [
                'nik' => '3471010505050005',
                'nama_pengunjung' => 'Joko Widodo',
                'no_hp' => '081234567005',
                'waktu_daftar' => Carbon::now(),
            ],
        ];

        foreach ($pengunjungs as $data) {
            Pengunjung::create($data);
        }

        // Create sample antrian for today
        $layanans = Layanan::all();
        $pengunjungList = Pengunjung::all();
        $admin = Admin::first();

        // Create antrian for today
        foreach ($pengunjungList->take(8) as $index => $pengunjung) {
            $layanan = $layanans->random();
            
            // Generate nomor antrian
            $today = Carbon::today();
            $lastNumber = Antrian::whereDate('waktu_antrian', $today)
                ->where('id_layanan', $layanan->id_layanan)
                ->count();
            
            $nomorAntrian = $layanan->kode_layanan . '-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            
            // Tentukan status berdasarkan urutan
            $status = 'menunggu';
            $waktuDipanggil = null;
            $idAdmin = null;
            
            if ($index == 0) {
                $status = 'dipanggil';
                $waktuDipanggil = Carbon::now()->subMinutes(5);
                $idAdmin = $admin->id_admin;
            } elseif ($index < 3) {
                $status = 'selesai';
                $waktuDipanggil = Carbon::now()->subHour();
                $idAdmin = $admin->id_admin;
            } elseif ($index == 7) {
                $status = 'batal';
                $idAdmin = $admin->id_admin;
            }

            $waktuAntrian = Carbon::now()->subHours(4)->addMinutes($index * 15);
            $waktuEstimasi = $waktuAntrian->copy()->addMinutes($layanan->estimasi_durasi_layanan * ($index + 1));

            Antrian::create([
                'nomor_antrian' => $nomorAntrian,
                'waktu_antrian' => $waktuAntrian,
                'status_antrian' => $status,
                'waktu_estimasi' => $waktuEstimasi,
                'waktu_dipanggil' => $waktuDipanggil,
                'id_admin' => $idAdmin,
                'id_pengunjung' => $pengunjung->id_pengunjung,
                'id_layanan' => $layanan->id_layanan,
            ]);
        }

        // Create antrian for yesterday (completed)
        $yesterday = Carbon::yesterday();
        foreach ($pengunjungList->take(15) as $index => $pengunjung) {
            $layanan = $layanans->random();
            
            $lastNumber = Antrian::whereDate('waktu_antrian', $yesterday)
                ->where('id_layanan', $layanan->id_layanan)
                ->count();
            
            $nomorAntrian = $layanan->kode_layanan . '-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
            
            $waktuAntrian = $yesterday->copy()->addHours(8)->addMinutes($index * 20);
            $waktuDipanggil = $waktuAntrian->copy()->addMinutes(30);

            Antrian::create([
                'nomor_antrian' => $nomorAntrian,
                'waktu_antrian' => $waktuAntrian,
                'status_antrian' => $index < 12 ? 'selesai' : ($index == 14 ? 'batal' : 'selesai'),
                'waktu_estimasi' => $waktuAntrian->copy()->addMinutes($layanan->estimasi_durasi_layanan),
                'waktu_dipanggil' => $waktuDipanggil,
                'id_admin' => $admin->id_admin,
                'id_pengunjung' => $pengunjung->id_pengunjung,
                'id_layanan' => $layanan->id_layanan,
                'created_at' => $waktuAntrian,
                'updated_at' => $waktuDipanggil->copy()->addMinutes($layanan->estimasi_durasi_layanan),
            ]);
        }
    }
}