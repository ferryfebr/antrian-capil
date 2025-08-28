<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Layanan;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LayananController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Layanan::with('admin');

        // Filter berdasarkan status aktif
        if ($request->filled('status')) {
            if ($request->status == 'aktif') {
                $query->where('aktif', true);
            } elseif ($request->status == 'tidak_aktif') {
                $query->where('aktif', false);
            }
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_layanan', 'like', "%{$search}%")
                  ->orWhere('kode_layanan', 'like', "%{$search}%");
            });
        }

        $layanans = $query->orderBy('created_at', 'desc')->paginate(10);
        
        return view('layanan.index', compact('layanans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $admins = Admin::all();
        return view('layanan.create', compact('admins'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_layanan' => 'required|max:100|unique:layanan,nama_layanan',
            'kode_layanan' => 'required|max:10|unique:layanan,kode_layanan',
            'estimasi_durasi_layanan' => 'required|integer|min:1|max:999',
            'kapasitas_harian' => 'required|integer|min:1|max:999',
            'aktif' => 'boolean',
            'id_admin' => 'nullable|exists:admin,id_admin'
        ], [
            'nama_layanan.required' => 'Nama layanan wajib diisi',
            'nama_layanan.unique' => 'Nama layanan sudah ada',
            'kode_layanan.required' => 'Kode layanan wajib diisi',
            'kode_layanan.unique' => 'Kode layanan sudah digunakan',
            'estimasi_durasi_layanan.required' => 'Estimasi durasi layanan wajib diisi',
            'estimasi_durasi_layanan.integer' => 'Estimasi durasi harus berupa angka',
            'estimasi_durasi_layanan.min' => 'Estimasi durasi minimal 1 menit',
            'kapasitas_harian.required' => 'Kapasitas harian wajib diisi',
            'kapasitas_harian.integer' => 'Kapasitas harian harus berupa angka',
        ]);

        Layanan::create([
            'nama_layanan' => $request->nama_layanan,
            'kode_layanan' => strtoupper($request->kode_layanan),
            'estimasi_durasi_layanan' => $request->estimasi_durasi_layanan,
            'kapasitas_harian' => $request->kapasitas_harian,
            'aktif' => $request->has('aktif') ? true : false,
            'id_admin' => $request->id_admin,
        ]);

        return redirect()->route('layanan.index')->with('success', 'Layanan berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $layanan = Layanan::with(['admin', 'loket', 'antrian.pengunjung'])->findOrFail($id);
        
        // Statistik layanan
        $today = \Carbon\Carbon::today();
        $stats = [
            'total_antrian_hari_ini' => $layanan->antrian()->whereDate('waktu_antrian', $today)->count(),
            'antrian_menunggu' => $layanan->antrian()->whereDate('waktu_antrian', $today)->where('status_antrian', 'menunggu')->count(),
            'antrian_selesai' => $layanan->antrian()->whereDate('waktu_antrian', $today)->where('status_antrian', 'selesai')->count(),
            'antrian_batal' => $layanan->antrian()->whereDate('waktu_antrian', $today)->where('status_antrian', 'batal')->count(),
            'total_loket' => $layanan->loket()->count(),
            'loket_aktif' => $layanan->loket()->where('status_loket', 'aktif')->count(),
        ];

        // Antrian terbaru untuk layanan ini
        $antrian_terbaru = $layanan->antrian()
            ->with('pengunjung')
            ->whereDate('waktu_antrian', $today)
            ->orderBy('waktu_antrian', 'desc')
            ->limit(10)
            ->get();

        return view('layanan.show', compact('layanan', 'stats', 'antrian_terbaru'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $layanan = Layanan::findOrFail($id);
        $admins = Admin::all();
        return view('layanan.edit', compact('layanan', 'admins'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $layanan = Layanan::findOrFail($id);
        
        $request->validate([
            'nama_layanan' => 'required|max:100|unique:layanan,nama_layanan,' . $layanan->id_layanan . ',id_layanan',
            'kode_layanan' => 'required|max:10|unique:layanan,kode_layanan,' . $layanan->id_layanan . ',id_layanan',
            'estimasi_durasi_layanan' => 'required|integer|min:1|max:999',
            'kapasitas_harian' => 'required|integer|min:1|max:999',
            'aktif' => 'boolean',
            'id_admin' => 'nullable|exists:admin,id_admin'
        ], [
            'nama_layanan.required' => 'Nama layanan wajib diisi',
            'nama_layanan.unique' => 'Nama layanan sudah ada',
            'kode_layanan.required' => 'Kode layanan wajib diisi',
            'kode_layanan.unique' => 'Kode layanan sudah digunakan',
            'estimasi_durasi_layanan.required' => 'Estimasi durasi layanan wajib diisi',
            'estimasi_durasi_layanan.integer' => 'Estimasi durasi harus berupa angka',
            'estimasi_durasi_layanan.min' => 'Estimasi durasi minimal 1 menit',
            'kapasitas_harian.required' => 'Kapasitas harian wajib diisi',
            'kapasitas_harian.integer' => 'Kapasitas harian harus berupa angka',
        ]);

        $layanan->update([
            'nama_layanan' => $request->nama_layanan,
            'kode_layanan' => strtoupper($request->kode_layanan),
            'estimasi_durasi_layanan' => $request->estimasi_durasi_layanan,
            'kapasitas_harian' => $request->kapasitas_harian,
            'aktif' => $request->has('aktif') ? true : false,
            'id_admin' => $request->id_admin,
        ]);

        return redirect()->route('layanan.index')->with('success', 'Layanan berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $layanan = Layanan::findOrFail($id);
        
        // Cek apakah layanan memiliki antrian aktif
        $hasActiveQueue = $layanan->antrian()
            ->whereIn('status_antrian', ['menunggu', 'dipanggil'])
            ->exists();
            
        if ($hasActiveQueue) {
            return redirect()->route('layanan.index')
                ->with('error', 'Tidak dapat menghapus layanan yang masih memiliki antrian aktif!');
        }

        // Cek apakah layanan memiliki loket
        $hasLoket = $layanan->loket()->exists();
        if ($hasLoket) {
            return redirect()->route('layanan.index')
                ->with('error', 'Tidak dapat menghapus layanan yang masih memiliki loket! Hapus loket terlebih dahulu.');
        }

        $layanan->delete();

        return redirect()->route('layanan.index')->with('success', 'Layanan berhasil dihapus!');
    }

    /**
     * Toggle layanan status (aktif/tidak aktif)
     */
    public function toggleStatus($id)
    {
        $layanan = Layanan::findOrFail($id);
        
        // Jika akan dinonaktifkan, cek apakah ada antrian aktif
        if ($layanan->aktif) {
            $hasActiveQueue = $layanan->antrian()
                ->whereIn('status_antrian', ['menunggu', 'dipanggil'])
                ->exists();
                
            if ($hasActiveQueue) {
                return back()->with('error', 'Tidak dapat menonaktifkan layanan yang masih memiliki antrian aktif!');
            }
        }

        $layanan->update(['aktif' => !$layanan->aktif]);

        $status = $layanan->aktif ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Layanan {$layanan->nama_layanan} berhasil {$status}!");
    }

    /**
     * Get layanan data for AJAX
     */
    public function getLayananData($id)
    {
        $layanan = Layanan::findOrFail($id);
        return response()->json([
            'id_layanan' => $layanan->id_layanan,
            'nama_layanan' => $layanan->nama_layanan,
            'kode_layanan' => $layanan->kode_layanan,
            'estimasi_durasi_layanan' => $layanan->estimasi_durasi_layanan,
            'kapasitas_harian' => $layanan->kapasitas_harian,
            'aktif' => $layanan->aktif,
        ]);
    }

    /**
     * Bulk actions for multiple layanan
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'layanan_ids' => 'required|array|min:1',
            'layanan_ids.*' => 'exists:layanan,id_layanan'
        ]);

        $layananIds = $request->layanan_ids;
        $action = $request->action;
        $processed = 0;
        $errors = [];

        foreach ($layananIds as $id) {
            $layanan = Layanan::find($id);
            if (!$layanan) continue;

            try {
                switch ($action) {
                    case 'activate':
                        $layanan->update(['aktif' => true]);
                        $processed++;
                        break;
                    
                    case 'deactivate':
                        // Cek antrian aktif
                        $hasActiveQueue = $layanan->antrian()
                            ->whereIn('status_antrian', ['menunggu', 'dipanggil'])
                            ->exists();
                        
                        if ($hasActiveQueue) {
                            $errors[] = "Layanan {$layanan->nama_layanan} memiliki antrian aktif";
                        } else {
                            $layanan->update(['aktif' => false]);
                            $processed++;
                        }
                        break;
                    
                    case 'delete':
                        // Cek antrian aktif dan loket
                        $hasActiveQueue = $layanan->antrian()
                            ->whereIn('status_antrian', ['menunggu', 'dipanggil'])
                            ->exists();
                        $hasLoket = $layanan->loket()->exists();
                        
                        if ($hasActiveQueue) {
                            $errors[] = "Layanan {$layanan->nama_layanan} memiliki antrian aktif";
                        } elseif ($hasLoket) {
                            $errors[] = "Layanan {$layanan->nama_layanan} masih memiliki loket";
                        } else {
                            $layanan->delete();
                            $processed++;
                        }
                        break;
                }
            } catch (\Exception $e) {
                $errors[] = "Gagal memproses layanan {$layanan->nama_layanan}: " . $e->getMessage();
            }
        }

        $message = "Berhasil memproses {$processed} layanan.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', $errors);
        }

        return back()->with($processed > 0 ? 'success' : 'error', $message);
    }

    /**
     * Export layanan data
     */
    public function export(Request $request)
    {
        $query = Layanan::with('admin');

        if ($request->filled('status')) {
            if ($request->status == 'aktif') {
                $query->where('aktif', true);
            } elseif ($request->status == 'tidak_aktif') {
                $query->where('aktif', false);
            }
        }

        $layanans = $query->get();

        $filename = 'layanan_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($layanans) {
            $file = fopen('php://output', 'w');
            
            // Header CSV
            fputcsv($file, [
                'ID',
                'Nama Layanan',
                'Kode Layanan', 
                'Estimasi Durasi (menit)',
                'Kapasitas Harian',
                'Status',
                'Admin',
                'Dibuat',
                'Diperbarui'
            ]);
            
            // Data
            foreach ($layanans as $layanan) {
                fputcsv($file, [
                    $layanan->id_layanan,
                    $layanan->nama_layanan,
                    $layanan->kode_layanan,
                    $layanan->estimasi_durasi_layanan,
                    $layanan->kapasitas_harian,
                    $layanan->aktif ? 'Aktif' : 'Tidak Aktif',
                    $layanan->admin ? $layanan->admin->nama_admin : '-',
                    $layanan->created_at->format('d/m/Y H:i'),
                    $layanan->updated_at->format('d/m/Y H:i')
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}