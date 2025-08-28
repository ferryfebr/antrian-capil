<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Antrian;
use App\Models\Layanan;
use App\Models\Pengunjung;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PublicQueueController extends Controller
{
    /**
     * Show public homepage
     */
    public function index()
    {
        $layanans = Layanan::where('aktif', true)->get();
        return view('public.index', compact('layanans'));
    }

    /**
     * Store queue from public form
     */
    public function store(Request $request)
    {
        $request->validate([
            'nik' => 'required|digits:16',
            'nama_pengunjung' => 'required|max:100',
            'no_hp' => 'nullable|max:15',
            'id_layanan' => 'required|exists:layanan,id_layanan'
        ]);

        // Cari atau buat pengunjung baru
        $pengunjung = Pengunjung::firstOrCreate(
            ['nik' => $request->nik],
            [
                'nama_pengunjung' => $request->nama_pengunjung,
                'no_hp' => $request->no_hp,
                'waktu_daftar' => now()
            ]
        );

        $layanan = Layanan::findOrFail($request->id_layanan);

        // Generate nomor antrian
        $today = Carbon::today();
        $lastNumber = Antrian::whereDate('waktu_antrian', $today)
            ->where('id_layanan', $request->id_layanan)
            ->count();
        
        $nomorAntrian = $layanan->kode_layanan . '-' . str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);

        // Hitung estimasi waktu
        $antrianMenunggu = Antrian::whereDate('waktu_antrian', $today)
            ->where('id_layanan', $request->id_layanan)
            ->where('status_antrian', 'menunggu')
            ->count();

        $waktuEstimasi = now()->addMinutes($antrianMenunggu * $layanan->estimasi_durasi_layanan);

        // Simpan antrian
        $antrian = Antrian::create([
            'nomor_antrian' => $nomorAntrian,
            'waktu_antrian' => now(),
            'status_antrian' => 'menunggu',
            'waktu_estimasi' => $waktuEstimasi,
            'id_pengunjung' => $pengunjung->id_pengunjung,
            'id_layanan' => $request->id_layanan,
        ]);

        return view('public.ticket', compact('antrian'));
    }

    /**
     * Show queue ticket
     */
    public function showTicket($id)
    {
        $antrian = Antrian::with(['pengunjung', 'layanan'])->findOrFail($id);
        return view('public.ticket', compact('antrian'));
    }
}