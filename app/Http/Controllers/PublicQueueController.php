<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\Pengunjung;
use App\Models\Antrian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PublicQueueController extends Controller
{
    public function index()
    {
        try {
            // PERBAIKAN: Pastikan mengambil layanan yang aktif saja
            $layanans = Layanan::where('aktif', true)
                ->orderBy('nama_layanan', 'asc')
                ->get();

            // Log untuk debugging
            Log::info('Public layanan loaded:', [
                'total_layanan_aktif' => $layanans->count(),
                'layanan_ids' => $layanans->pluck('id_layanan')->toArray()
            ]);

            // Jika tidak ada layanan aktif, log warning
            if ($layanans->count() == 0) {
                Log::warning('No active layanan found for public display');
            }

            return view('public.index', compact('layanans'));

        } catch (\Exception $e) {
            Log::error('Public index error: ' . $e->getMessage());
            
            // Return with empty collection if error
            return view('public.index', ['layanans' => collect()]);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nik' => 'required|string|size:16',
                'nama_pengunjung' => 'required|string|max:100',
                'no_hp' => 'nullable|string|max:15',
                'id_layanan' => 'required|exists:layanan,id_layanan'
            ]);

            DB::beginTransaction();

            // Check if layanan exists and is active
            $layanan = Layanan::findOrFail($request->id_layanan);
            if (!$layanan->aktif) {
                throw new \Exception('Layanan tidak aktif');
            }

            // Create or update pengunjung
            $pengunjung = Pengunjung::updateOrCreate(
                ['nik' => $request->nik],
                [
                    'nama_pengunjung' => $request->nama_pengunjung,
                    'no_hp' => $request->no_hp,
                    'waktu_daftar' => now()
                ]
            );

            // Generate nomor antrian
            $nomorAntrian = $this->generateNomorAntrian($layanan);

            // Create antrian
            $antrian = Antrian::create([
                'nomor_antrian' => $nomorAntrian,
                'id_pengunjung' => $pengunjung->id_pengunjung,
                'id_layanan' => $layanan->id_layanan,
                'waktu_antrian' => now(),
                'status_antrian' => 'menunggu',
                'waktu_estimasi' => now()->addMinutes($layanan->estimasi_durasi_layanan)
            ]);

            DB::commit();

            // Debug log
            Log::info('Antrian created successfully', [
                'antrian_id' => $antrian->id_antrian,
                'nomor_antrian' => $antrian->nomor_antrian
            ]);

            return redirect()->route('public.ticket', ['id' => $antrian->id_antrian])
                           ->with('success', 'Antrian berhasil dibuat');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create antrian: ' . $e->getMessage());
            return back()->withInput()
                        ->withErrors(['error' => 'Gagal membuat antrian: ' . $e->getMessage()]);
        }
    }

    public function showTicket($id)
    {
        try {
            $antrian = Antrian::with(['pengunjung', 'layanan'])
                ->findOrFail($id);

            return view('public.ticket', compact('antrian'));

        } catch (\Exception $e) {
            Log::error('Show ticket error: ' . $e->getMessage());
            
            return redirect()->route('public.index')
                ->with('error', 'Tiket tidak ditemukan');
        }
    }

    private function generateNomorAntrian(Layanan $layanan)
    {
        try {
            // Ambil nomor urut untuk layanan ini hari ini
            $count = Antrian::where('id_layanan', $layanan->id_layanan)
                ->whereDate('waktu_antrian', Carbon::today())
                ->count();

            $urutan = $count + 1;
            $nomorAntrian = $layanan->kode_layanan . '-' . str_pad($urutan, 3, '0', STR_PAD_LEFT);

            // Pastikan nomor antrian unik
            while (Antrian::where('nomor_antrian', $nomorAntrian)
                ->whereDate('waktu_antrian', Carbon::today())
                ->exists()) {
                
                $urutan++;
                $nomorAntrian = $layanan->kode_layanan . '-' . str_pad($urutan, 3, '0', STR_PAD_LEFT);
            }

            return $nomorAntrian;

        } catch (\Exception $e) {
            Log::error('Generate nomor antrian error: ' . $e->getMessage());
            
            // Fallback: generate with timestamp
            return $layanan->kode_layanan . '-' . Carbon::now()->format('His');
        }
    }

    /**
     * Get available services for AJAX requests
     */
    public function getAvailableServices()
    {
        try {
            $layanans = Layanan::where('aktif', true)
                ->select('id_layanan', 'nama_layanan', 'kode_layanan', 'kapasitas_harian', 'estimasi_durasi_layanan')
                ->get()
                ->map(function($layanan) {
                    $antrianHariIni = Antrian::where('id_layanan', $layanan->id_layanan)
                        ->whereDate('waktu_antrian', Carbon::today())
                        ->count();

                    return [
                        'id_layanan' => $layanan->id_layanan,
                        'nama_layanan' => $layanan->nama_layanan,
                        'kode_layanan' => $layanan->kode_layanan,
                        'kapasitas_harian' => $layanan->kapasitas_harian,
                        'estimasi_durasi_layanan' => $layanan->estimasi_durasi_layanan,
                        'antrian_hari_ini' => $antrianHariIni,
                        'sisa_kapasitas' => $layanan->kapasitas_harian - $antrianHariIni,
                        'tersedia' => $antrianHariIni < $layanan->kapasitas_harian
                    ];
                });

            return response()->json($layanans);

        } catch (\Exception $e) {
            Log::error('Get available services error: ' . $e->getMessage());
            
            return response()->json([], 500);
        }
    }
}