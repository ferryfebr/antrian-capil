<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use App\Models\Layanan;
use App\Models\Pengunjung;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AntrianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Antrian::with(['pengunjung', 'layanan', 'admin']);

        // Filter berdasarkan tanggal
        if ($request->filled('tanggal')) {
            $query->whereDate('waktu_antrian', $request->tanggal);
        } else {
            // Default hari ini
            $query->whereDate('waktu_antrian', Carbon::today());
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status_antrian', $request->status);
        }

        // Filter berdasarkan layanan
        if ($request->filled('layanan')) {
            $query->where('id_layanan', $request->layanan);
        }

        // Filter berdasarkan admin
        if ($request->filled('admin')) {
            $query->where('id_admin', $request->admin);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_antrian', 'like', "%{$search}%")
                  ->orWhereHas('pengunjung', function($pq) use ($search) {
                      $pq->where('nama_pengunjung', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%");
                  });
            });
        }

        $antrians = $query->orderBy('waktu_antrian', 'desc')->paginate(15);
        $layanans = Layanan::where('aktif', true)->get();

        return view('antrian.index', compact('antrians', 'layanans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $layanans = Layanan::where('aktif', true)->get();
        
        // Pre-fill NIK if provided in request
        $prefilledNik = $request->get('nik');
        $prefilledLayanan = $request->get('layanan');

        return view('antrian.create', compact('layanans', 'prefilledNik', 'prefilledLayanan'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nik' => 'required|digits:16',
            'nama_pengunjung' => 'required|max:100',
            'no_hp' => 'nullable|max:15',
            'id_layanan' => 'required|exists:layanan,id_layanan'
        ], [
            'nik.required' => 'NIK wajib diisi',
            'nik.digits' => 'NIK harus 16 digit',
            'nama_pengunjung.required' => 'Nama pengunjung wajib diisi',
            'id_layanan.required' => 'Layanan wajib dipilih',
            'id_layanan.exists' => 'Layanan tidak valid'
        ]);

        DB::beginTransaction();
        
        try {
            // Create or update pengunjung
            $pengunjung = Pengunjung::createOrUpdateByNik([
                'nik' => $request->nik,
                'nama_pengunjung' => $request->nama_pengunjung,
                'no_hp' => $request->no_hp
            ]);

            $layanan = Layanan::findOrFail($request->id_layanan);

            // Check daily capacity
            $todayCount = Antrian::whereDate('waktu_antrian', today())
                ->where('id_layanan', $request->id_layanan)
                ->count();

            if ($todayCount >= $layanan->kapasitas_harian) {
                return back()->withErrors(['id_layanan' => 'Kapasitas layanan hari ini sudah penuh!'])
                           ->withInput();
            }

            // Generate nomor antrian
            $nomorAntrian = Antrian::generateQueueNumber($request->id_layanan);

            // Calculate estimated time
            $waktuEstimasi = $layanan->calculateEstimatedWaitTime();

            // Create antrian
            $antrian = Antrian::create([
                'nomor_antrian' => $nomorAntrian,
                'waktu_antrian' => now(),
                'status_antrian' => 'menunggu',
                'waktu_estimasi' => $waktuEstimasi,
                'id_pengunjung' => $pengunjung->id_pengunjung,
                'id_layanan' => $request->id_layanan,
            ]);

            DB::commit();

            // Log activity
            \Log::info('New queue created', [
                'antrian_id' => $antrian->id_antrian,
                'nomor_antrian' => $nomorAntrian,
                'pengunjung_nik' => $pengunjung->nik,
                'layanan' => $layanan->nama_layanan,
                'created_by_admin' => Auth::guard('admin')->check()
            ]);

            return redirect()->route('antrian.show', $antrian->id_antrian)
                ->with('success', "Antrian berhasil dibuat! Nomor antrian: {$nomorAntrian}");

        } catch (\Exception $e) {
            DB::rollback();
            
            \Log::error('Failed to create queue', [
                'error' => $e->getMessage(),
                'data' => $request->except('password')
            ]);

            return back()->withErrors(['error' => 'Gagal membuat antrian. Silakan coba lagi.'])
                        ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $antrian = Antrian::with(['pengunjung', 'layanan', 'admin'])->findOrFail($id);
        
        // Get queue position if still waiting
        $queuePosition = null;
        if ($antrian->status_antrian === 'menunggu') {
            $queuePosition = Antrian::where('id_layanan', $antrian->id_layanan)
                ->whereDate('waktu_antrian', $antrian->waktu_antrian->toDateString())
                ->where('status_antrian', 'menunggu')
                ->where('waktu_antrian', '<', $antrian->waktu_antrian)
                ->count() + 1;
        }

        return view('antrian.show', compact('antrian', 'queuePosition'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $antrian = Antrian::with(['pengunjung', 'layanan'])->findOrFail($id);
        $layanans = Layanan::where('aktif', true)->get();
        
        // Only allow editing if status is 'menunggu'
        if ($antrian->status_antrian !== 'menunggu') {
            return redirect()->route('antrian.show', $id)
                ->with('warning', 'Hanya antrian dengan status menunggu yang dapat diedit.');
        }

        return view('antrian.edit', compact('antrian', 'layanans'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $antrian = Antrian::findOrFail($id);
        
        // Only allow updating if status is 'menunggu'
        if ($antrian->status_antrian !== 'menunggu') {
            return redirect()->route('antrian.show', $id)
                ->with('error', 'Antrian ini tidak dapat diubah.');
        }

        $request->validate([
            'nama_pengunjung' => 'required|max:100',
            'no_hp' => 'nullable|max:15',
            'id_layanan' => 'required|exists:layanan,id_layanan'
        ]);

        DB::beginTransaction();
        
        try {
            // Update pengunjung data
            $antrian->pengunjung->update([
                'nama_pengunjung' => $request->nama_pengunjung,
                'no_hp' => $request->no_hp
            ]);

            // If layanan changed, update antrian
            if ($antrian->id_layanan != $request->id_layanan) {
                $newLayanan = Layanan::findOrFail($request->id_layanan);
                
                // Generate new queue number
                $nomorAntrian = Antrian::generateQueueNumber($request->id_layanan);
                
                // Calculate new estimated time
                $waktuEstimasi = $newLayanan->calculateEstimatedWaitTime();

                $antrian->update([
                    'nomor_antrian' => $nomorAntrian,
                    'id_layanan' => $request->id_layanan,
                    'waktu_estimasi' => $waktuEstimasi
                ]);
            }

            DB::commit();

            return redirect()->route('antrian.show', $antrian->id_antrian)
                ->with('success', 'Data antrian berhasil diperbarui!');

        } catch (\Exception $e) {
            DB::rollback();
            
            return back()->withErrors(['error' => 'Gagal memperbarui antrian.'])
                        ->withInput();
        }
    }

    /**
     * Update antrian status
     */
    public function updateStatus(Request $request, $id)
    {
        $antrian = Antrian::findOrFail($id);
        
        $request->validate([
            'status_antrian' => 'required|in:menunggu,dipanggil,selesai,batal'
        ]);

        $newStatus = $request->status_antrian;
        $currentStatus = $antrian->status_antrian;

        // Validate status transition
        if (!$antrian->canUpdateToStatus($newStatus)) {
            return back()->with('error', "Tidak dapat mengubah status dari {$currentStatus} ke {$newStatus}");
        }

        try {
            $adminId = Auth::guard('admin')->id();
            
            switch ($newStatus) {
                case 'dipanggil':
                    $antrian->callQueue($adminId);
                    $message = 'Antrian berhasil dipanggil!';
                    break;
                    
                case 'selesai':
                    $antrian->completeQueue($adminId);
                    $message = 'Antrian berhasil diselesaikan!';
                    break;
                    
                case 'batal':
                    $antrian->cancelQueue($adminId);
                    $message = 'Antrian berhasil dibatalkan!';
                    break;
                    
                case 'menunggu':
                    $antrian->resetQueue();
                    $message = 'Antrian dikembalikan ke status menunggu!';
                    break;
                    
                default:
                    return back()->with('error', 'Status tidak valid');
            }

            // Log status change
            \Log::info('Queue status updated', [
                'antrian_id' => $antrian->id_antrian,
                'nomor_antrian' => $antrian->nomor_antrian,
                'old_status' => $currentStatus,
                'new_status' => $newStatus,
                'admin_id' => $adminId
            ]);

            return back()->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Failed to update queue status', [
                'error' => $e->getMessage(),
                'antrian_id' => $id,
                'status' => $newStatus
            ]);

            return back()->with('error', 'Gagal mengubah status antrian.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $antrian = Antrian::findOrFail($id);
        
        try {
            $nomorAntrian = $antrian->nomor_antrian;
            $antrian->delete();

            \Log::info('Queue deleted', [
                'antrian_id' => $id,
                'nomor_antrian' => $nomorAntrian,
                'deleted_by' => Auth::guard('admin')->id()
            ]);

            return redirect()->route('antrian.index')
                ->with('success', "Antrian {$nomorAntrian} berhasil dihapus!");

        } catch (\Exception $e) {
            \Log::error('Failed to delete queue', [
                'error' => $e->getMessage(),
                'antrian_id' => $id
            ]);

            return back()->with('error', 'Gagal menghapus antrian.');
        }
    }

    /**
     * Display public queue screen
     */
    public function publicQueue()
    {
        $today = Carbon::today();
        
        $antrians = Antrian::with(['pengunjung', 'layanan'])
            ->whereDate('waktu_antrian', $today)
            ->whereIn('status_antrian', ['menunggu', 'dipanggil', 'selesai'])
            ->orderBy('waktu_antrian', 'asc')
            ->get();

        return view('public.queue', compact('antrians'));
    }

    /**
     * Get current queue data for API
     */
    public function getCurrentQueue()
    {
        $currentQueue = Antrian::with(['pengunjung', 'layanan'])
            ->whereDate('waktu_antrian', today())
            ->where('status_antrian', 'dipanggil')
            ->latest('waktu_dipanggil')
            ->first();

        $waitingQueues = Antrian::with(['pengunjung', 'layanan'])
            ->whereDate('waktu_antrian', today())
            ->where('status_antrian', 'menunggu')
            ->orderBy('waktu_antrian', 'asc')
            ->limit(10)
            ->get();

        return response()->json([
            'current' => $currentQueue,
            'waiting' => $waitingQueues,
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Call next queue in line
     */
    public function callNext(Request $request)
    {
        $layananId = $request->get('layanan_id');
        
        $query = Antrian::whereDate('waktu_antrian', today())
            ->where('status_antrian', 'menunggu');
        
        if ($layananId) {
            $query->where('id_layanan', $layananId);
        }
        
        $nextQueue = $query->orderBy('waktu_antrian', 'asc')->first();
        
        if (!$nextQueue) {
            return response()->json(['error' => 'Tidak ada antrian yang menunggu'], 404);
        }

        try {
            $nextQueue->callQueue(Auth::guard('admin')->id());
            
            return response()->json([
                'success' => true,
                'antrian' => $nextQueue->load(['pengunjung', 'layanan']),
                'message' => "Antrian {$nextQueue->nomor_antrian} berhasil dipanggil"
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal memanggil antrian'], 500);
        }
    }

    /**
     * Get queue statistics
     */
    public function getStats(Request $request)
    {
        $date = $request->get('date', today()->toDateString());
        $targetDate = Carbon::parse($date);
        
        $stats = [
            'total' => Antrian::whereDate('waktu_antrian', $targetDate)->count(),
            'menunggu' => Antrian::whereDate('waktu_antrian', $targetDate)->where('status_antrian', 'menunggu')->count(),
            'dipanggil' => Antrian::whereDate('waktu_antrian', $targetDate)->where('status_antrian', 'dipanggil')->count(),
            'selesai' => Antrian::whereDate('waktu_antrian', $targetDate)->where('status_antrian', 'selesai')->count(),
            'batal' => Antrian::whereDate('waktu_antrian', $targetDate)->where('status_antrian', 'batal')->count(),
        ];

        // Service breakdown
        $serviceBreakdown = Antrian::whereDate('waktu_antrian', $targetDate)
            ->select('id_layanan', 'status_antrian', DB::raw('count(*) as total'))
            ->with('layanan:id_layanan,nama_layanan,kode_layanan')
            ->groupBy('id_layanan', 'status_antrian')
            ->get()
            ->groupBy('id_layanan');

        return response()->json([
            'stats' => $stats,
            'service_breakdown' => $serviceBreakdown,
            'date' => $targetDate->format('Y-m-d')
        ]);
    }

    /**
     * Export queue data
     */
    public function export(Request $request)
    {
        $date = $request->get('date', today()->toDateString());
        $targetDate = Carbon::parse($date);
        
        $antrians = Antrian::with(['pengunjung', 'layanan', 'admin'])
            ->whereDate('waktu_antrian', $targetDate)
            ->orderBy('waktu_antrian', 'asc')
            ->get();

        $filename = 'antrian_' . $targetDate->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($antrians, $targetDate) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, [
                'Data Antrian - ' . $targetDate->format('d/m/Y')
            ]);
            fputcsv($file, []);
            
            // Column headers
            fputcsv($file, [
                'No. Antrian',
                'NIK',
                'Nama Pengunjung',
                'No. HP',
                'Layanan',
                'Status',
                'Waktu Antri',
                'Waktu Estimasi',
                'Waktu Dipanggil',
                'Admin',
                'Waktu Update'
            ]);
            
            // Data
            foreach ($antrians as $antrian) {
                fputcsv($file, [
                    $antrian->nomor_antrian,
                    $antrian->pengunjung->nik,
                    $antrian->pengunjung->nama_pengunjung,
                    $antrian->pengunjung->no_hp ?? '-',
                    $antrian->layanan->nama_layanan,
                    ucfirst($antrian->status_antrian),
                    $antrian->waktu_antrian->format('d/m/Y H:i:s'),
                    $antrian->waktu_estimasi ? $antrian->waktu_estimasi->format('H:i') : '-',
                    $antrian->waktu_dipanggil ? $antrian->waktu_dipanggil->format('H:i:s') : '-',
                    $antrian->admin ? $antrian->admin->nama_admin : '-',
                    $antrian->updated_at->format('d/m/Y H:i:s')
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}