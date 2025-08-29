<?php

namespace App\Http\Controllers;

use App\Models\Layanan;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class LayananController extends Controller
{
    public function index()
    {
        $layanans = Layanan::with(['admin'])
            ->withCount('antrian')
            ->when(request('search'), function($query) {
                $query->where('nama_layanan', 'like', '%' . request('search') . '%')
                      ->orWhere('kode_layanan', 'like', '%' . request('search') . '%');
            })
            ->when(request('status'), function($query) {
                if (request('status') == 'aktif') {
                    $query->where('aktif', true);
                } elseif (request('status') == 'tidak_aktif') {
                    $query->where('aktif', false);
                }
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('layanan.index', compact('layanans'));
    }

    public function create()
    {
        $admins = Admin::orderBy('nama_admin', 'asc')->get();
        return view('layanan.create', compact('admins'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_layanan' => 'required|string|max:100',
            'kode_layanan' => 'required|string|max:10|unique:layanan,kode_layanan',
            'estimasi_durasi_layanan' => 'required|integer|min:1|max:999',
            'kapasitas_harian' => 'required|integer|min:1|max:999',
            'id_admin' => 'nullable|exists:admin,id_admin',
        ]);

        try {
            $layanan = new Layanan();
            $layanan->nama_layanan = trim($request->nama_layanan);
            $layanan->kode_layanan = strtoupper(trim($request->kode_layanan));
            $layanan->estimasi_durasi_layanan = $request->estimasi_durasi_layanan;
            $layanan->kapasitas_harian = $request->kapasitas_harian;
            $layanan->id_admin = $request->id_admin;
            
            // PENTING: Set status aktif berdasarkan checkbox
            $layanan->aktif = $request->has('aktif') ? 1 : 0;
            
            $layanan->save();

            // Clear cache untuk refresh data
            $this->clearLayananCache();

            // Log untuk debugging
            Log::info('Layanan baru ditambahkan: ', [
                'id' => $layanan->id_layanan,
                'nama' => $layanan->nama_layanan,
                'kode' => $layanan->kode_layanan,
                'aktif' => $layanan->aktif
            ]);

            return redirect()->route('layanan.index')
                ->with('success', 'Layanan "' . $layanan->nama_layanan . '" berhasil ditambahkan');

        } catch (\Exception $e) {
            Log::error('Error creating layanan: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'Gagal menambahkan layanan: ' . $e->getMessage());
        }
    }

    public function show(Layanan $layanan)
    {
        $layanan->load(['admin', 'antrian.pengunjung', 'loket']);
        
        // Statistik layanan
        $stats = [
            'total_antrian_hari_ini' => $layanan->antrian()->whereDate('waktu_antrian', today())->count(),
            'antrian_menunggu' => $layanan->antrian()->where('status_antrian', 'menunggu')->whereDate('waktu_antrian', today())->count(),
            'antrian_selesai' => $layanan->antrian()->where('status_antrian', 'selesai')->whereDate('waktu_antrian', today())->count(),
            'antrian_batal' => $layanan->antrian()->where('status_antrian', 'batal')->whereDate('waktu_antrian', today())->count(),
            'total_loket' => $layanan->loket()->count(),
            'loket_aktif' => $layanan->loket()->where('status_loket', 'aktif')->count(),
        ];

        // Antrian terbaru hari ini
        $antrian_terbaru = $layanan->antrian()
            ->with(['pengunjung'])
            ->whereDate('waktu_antrian', today())
            ->orderBy('waktu_antrian', 'desc')
            ->limit(10)
            ->get();

        return view('layanan.show', compact('layanan', 'stats', 'antrian_terbaru'));
    }

    public function edit(Layanan $layanan)
    {
        $admins = Admin::orderBy('nama_admin', 'asc')->get();
        return view('layanan.edit', compact('layanan', 'admins'));
    }

    public function update(Request $request, Layanan $layanan)
    {
        $request->validate([
            'nama_layanan' => 'required|string|max:100',
            'kode_layanan' => 'required|string|max:10|unique:layanan,kode_layanan,' . $layanan->id_layanan . ',id_layanan',
            'estimasi_durasi_layanan' => 'required|integer|min:1|max:999',
            'kapasitas_harian' => 'required|integer|min:1|max:999',
            'id_admin' => 'nullable|exists:admin,id_admin',
        ]);

        try {
            $layanan->nama_layanan = trim($request->nama_layanan);
            $layanan->kode_layanan = strtoupper(trim($request->kode_layanan));
            $layanan->estimasi_durasi_layanan = $request->estimasi_durasi_layanan;
            $layanan->kapasitas_harian = $request->kapasitas_harian;
            $layanan->id_admin = $request->id_admin;
            
            // PENTING: Update status aktif
            $layanan->aktif = $request->has('aktif') ? 1 : 0;
            
            $layanan->save();

            // Clear cache
            $this->clearLayananCache();

            // Log untuk debugging
            Log::info('Layanan diupdate: ', [
                'id' => $layanan->id_layanan,
                'nama' => $layanan->nama_layanan,
                'aktif' => $layanan->aktif
            ]);

            return redirect()->route('layanan.index')
                ->with('success', 'Layanan "' . $layanan->nama_layanan . '" berhasil diperbarui');

        } catch (\Exception $e) {
            Log::error('Error updating layanan: ' . $e->getMessage());
            
            return back()->withInput()
                ->with('error', 'Gagal memperbarui layanan: ' . $e->getMessage());
        }
    }

    public function toggleStatus(Layanan $layanan)
    {
        try {
            $layanan->aktif = !$layanan->aktif;
            $layanan->save();

            // Clear cache
            $this->clearLayananCache();

            $status = $layanan->aktif ? 'diaktifkan' : 'dinonaktifkan';
            
            Log::info("Layanan {$status}: {$layanan->nama_layanan}");

            return response()->json([
                'success' => true,
                'message' => "Layanan {$layanan->nama_layanan} berhasil {$status}",
                'status' => $layanan->aktif
            ]);

        } catch (\Exception $e) {
            Log::error('Error toggling layanan status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah status layanan'
            ], 500);
        }
    }

    public function destroy(Layanan $layanan)
    {
        try {
            // Cek apakah ada antrian yang terkait
            if ($layanan->antrian()->count() > 0) {
                return back()->with('error', 'Tidak dapat menghapus layanan yang memiliki data antrian');
            }

            $nama = $layanan->nama_layanan;
            $layanan->delete();

            // Clear cache
            $this->clearLayananCache();

            return redirect()->route('layanan.index')
                ->with('success', 'Layanan "' . $nama . '" berhasil dihapus');

        } catch (\Exception $e) {
            Log::error('Error deleting layanan: ' . $e->getMessage());
            
            return back()->with('error', 'Gagal menghapus layanan: ' . $e->getMessage());
        }
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'layanan_ids' => 'required|array',
            'layanan_ids.*' => 'exists:layanan,id_layanan'
        ]);

        try {
            $layanans = Layanan::whereIn('id_layanan', $request->layanan_ids)->get();
            $count = 0;

            foreach ($layanans as $layanan) {
                switch ($request->action) {
                    case 'activate':
                        $layanan->aktif = true;
                        $layanan->save();
                        $count++;
                        break;

                    case 'deactivate':
                        $layanan->aktif = false;
                        $layanan->save();
                        $count++;
                        break;

                    case 'delete':
                        if ($layanan->antrian()->count() == 0) {
                            $layanan->delete();
                            $count++;
                        }
                        break;
                }
            }

            // Clear cache
            $this->clearLayananCache();

            $actionText = [
                'activate' => 'diaktifkan',
                'deactivate' => 'dinonaktifkan',
                'delete' => 'dihapus'
            ];

            return redirect()->route('layanan.index')
                ->with('success', "{$count} layanan berhasil {$actionText[$request->action]}");

        } catch (\Exception $e) {
            Log::error('Error bulk action layanan: ' . $e->getMessage());
            
            return back()->with('error', 'Gagal melakukan aksi bulk: ' . $e->getMessage());
        }
    }

    public function export()
    {
        // Export functionality here
        return response()->json(['message' => 'Export feature coming soon']);
    }

    /**
     * Clear layanan related cache
     */
    private function clearLayananCache()
    {
        Cache::forget('layanan_aktif');
        Cache::forget('layanan_all');
        Cache::forget('dashboard_stats');
        Cache::forget('public_layanan');
    }
}