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
        $request->validate([
            'nik' => 'required|string|size:16',
            'nama_pengunjung' => 'required|string|max:100',
            'no_hp' => 'nullable|string|max:15',
            'id_layanan' => 'required|exists:layanan,id_layanan'
        ]);

        DB::beginTransaction();
        
        try {
            // PERBAIKAN: Pastikan layanan masih aktif dan tersedia
            $layanan = Layanan::where('id_layanan', $request->id_layanan)
                ->where('aktif', true)
                ->first();

            if (!$layanan) {
                return back()->with('error', 'Layanan tidak tersedia atau sedang tidak aktif')
                    ->withInput();
            }

            // Cek kapasitas harian
            $antrianHariIni = Antrian::where('id_layanan', $layanan->id_layanan)
                ->whereDate('waktu_antrian', Carbon::today())
                ->count();

            if ($antrianHariIni >= $layanan->kapasitas_harian) {
                return back()->with('error', 'Kapasitas layanan hari ini sudah penuh. Silakan coba lagi besok.')
                    ->withInput();
            }

            // Cari atau buat pengunjung
            $pengunjung = Pengunjung::where('nik', $request->nik)->first();
            
            if (!$pengunjung) {
                $pengunjung = new Pengunjung();
                $pengunjung->nik = $request->nik;
                $pengunjung->nama_pengunjung = trim($request->nama_pengunjung);
                $pengunjung->no_hp = $request->no_hp ? trim($request->no_hp) : null;
                $pengunjung->waktu_daftar = Carbon::now();
                $pengunjung->save();

                Log::info('New pengunjung created:', ['nik' => $pengunjung->nik, 'nama' => $pengunjung->nama_pengunjung]);
            } else {
                // Update data pengunjung jika ada perubahan
                $pengunjung->nama_pengunjung = trim($request->nama_pengunjung);
                if ($request->no_hp) {
                    $pengunjung->no_hp = trim($request->no_hp);
                }
                $pengunjung->save();

                Log::info('Pengunjung data updated:', ['nik' => $pengunjung->nik]);
            }

            // Cek apakah pengunjung sudah memiliki antrian aktif hari ini
            $antrianAktif = Antrian::where('id_pengunjung', $pengunjung->id_pengunjung)
                ->whereIn('status_antrian', ['menunggu', 'dipanggil'])
                ->whereDate('waktu_antrian', Carbon::today())
                ->first();

            if ($antrianAktif) {
                return back()->with('error', 'Anda sudah memiliki antrian aktif hari ini dengan nomor: ' . $antrianAktif->nomor_antrian)
                    ->withInput();
            }

            // Generate nomor antrian
            $nomorAntrian = $this->generateNomorAntrian($layanan);

            // Buat antrian baru
            $antrian = new Antrian();
            $antrian->nomor_antrian = $nomorAntrian;
            $antrian->id_pengunjung = $pengunjung->id_pengunjung;
            $antrian->id_layanan = $layanan->id_layanan;
            $antrian->waktu_antrian = Carbon::now();
            $antrian->status_antrian = 'menunggu';
            
            // Hitung estimasi waktu
            $antrianSebelum = Antrian::where('id_layanan', $layanan->id_layanan)
                ->whereDate('waktu_antrian', Carbon::today())
                ->where('status_antrian', 'menunggu')
                ->count();

            $estimasiMenit = $antrianSebelum * $layanan->estimasi_durasi_layanan;
            $antrian->waktu_estimasi = Carbon::now()->addMinutes($estimasiMenit);

            $antrian->save();

            DB::commit();

            Log::info('New antrian created:', [
                'nomor_antrian' => $antrian->nomor_antrian,
                'pengunjung' => $pengunjung->nama_pengunjung,
                'layanan' => $layanan->nama_layanan
            ]);

            // Redirect ke halaman tiket
            return redirect()->route('ticket.blade.php', $antrian->id_antrian)
                ->with('success', 'Antrian berhasil dibuat dengan nomor: ' . $antrian->nomor_antrian);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Public queue store error: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Gagal membuat antrian. Silakan coba lagi.')
                ->withInput();
        }
    }

    public function showTicket($id)
    {
        try {
            $antrian = Antrian::with(['pengunjung', 'layanan'])
                ->findOrFail($id);

            return view('public.tiket', compact('antrian'));

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