<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AntrianController;
use App\Http\Controllers\LayananController;
use App\Http\Controllers\LoketController;
use App\Http\Controllers\PengunjungController;
use App\Http\Controllers\PublicQueueController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public Routes
Route::get('/', [PublicQueueController::class, 'index'])->name('public.index');
Route::post('/ambil-antrian', [PublicQueueController::class, 'store'])->name('public.queue.store');
Route::get('/tiket/{id}', [PublicQueueController::class, 'showTicket'])->name('public.ticket');

// Public Queue Display
Route::get('/display-antrian', [AntrianController::class, 'publicQueue'])->name('public.queue');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// AJAX Routes for checking auth status
Route::get('/api/auth/check', [AuthController::class, 'checkAuth'])->name('auth.check');
Route::post('/api/auth/refresh', [AuthController::class, 'refreshSession'])->name('auth.refresh');

// Protected Routes (Admin Only)
Route::middleware(['auth.admin'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard/realtime-stats', [DashboardController::class, 'getRealtimeStats'])->name('dashboard.realtime');
    Route::get('/api/dashboard/queue-activity', [DashboardController::class, 'getQueueActivity'])->name('dashboard.activity');
    Route::get('/api/dashboard/service-stats', [DashboardController::class, 'getServiceStats'])->name('dashboard.service-stats');
    Route::get('/dashboard/export', [DashboardController::class, 'exportStats'])->name('dashboard.export');
    
    // Admin Management
    Route::resource('admin', AdminController::class);
    Route::post('/admin/{admin}/change-password', [AdminController::class, 'changePassword'])->name('admin.change-password');
    Route::get('/api/admin/{admin}/stats', [AdminController::class, 'getStats'])->name('admin.stats');
    Route::patch('/admin/{admin}/toggle-status', [AdminController::class, 'toggleStatus'])->name('admin.toggle-status');
    Route::get('/admin/export', [AdminController::class, 'export'])->name('admin.export');
    Route::post('/admin/bulk-delete', [AdminController::class, 'bulkDelete'])->name('admin.bulk-delete');
    
    // Antrian Management
    Route::resource('antrian', AntrianController::class);
    Route::patch('/antrian/{antrian}/update-status', [AntrianController::class, 'updateStatus'])->name('antrian.update-status');
    Route::post('/api/antrian/call-next', [AntrianController::class, 'callNext'])->name('antrian.call-next');
    Route::get('/api/antrian/current', [AntrianController::class, 'getCurrentQueue'])->name('antrian.current');
    Route::get('/api/antrian/stats', [AntrianController::class, 'getStats'])->name('antrian.stats');
    Route::get('/antrian/export', [AntrianController::class, 'export'])->name('antrian.export');
    
    // Layanan Management
    Route::resource('layanan', LayananController::class);
    Route::patch('/layanan/{layanan}/toggle-status', [LayananController::class, 'toggleStatus'])->name('layanan.toggle-status');
    Route::get('/api/layanan/{layanan}', [LayananController::class, 'getLayananData'])->name('layanan.data');
    Route::post('/layanan/bulk-action', [LayananController::class, 'bulkAction'])->name('layanan.bulk-action');
    Route::get('/layanan/export', [LayananController::class, 'export'])->name('layanan.export');
    
    // Loket Management
    Route::resource('loket', LoketController::class);
    
    // Pengunjung Management
    Route::resource('pengunjung', PengunjungController::class)->except(['create', 'store', 'edit', 'update']);
    
});

// API Routes for real-time updates (optional)
Route::prefix('api')->group(function () {
    // Public API - no authentication required
    Route::get('/antrian/today', function () {
        $antrians = \App\Models\Antrian::with(['pengunjung', 'layanan'])
            ->whereDate('waktu_antrian', \Carbon\Carbon::today())
            ->whereIn('status_antrian', ['menunggu', 'dipanggil'])
            ->orderBy('waktu_antrian', 'asc')
            ->get();
        return response()->json($antrians);
    });
    
    Route::get('/stats/today', function () {
        $today = \Carbon\Carbon::today();
        return response()->json([
            'total_antrian' => \App\Models\Antrian::whereDate('waktu_antrian', $today)->count(),
            'menunggu' => \App\Models\Antrian::whereDate('waktu_antrian', $today)->where('status_antrian', 'menunggu')->count(),
            'dipanggil' => \App\Models\Antrian::whereDate('waktu_antrian', $today)->where('status_antrian', 'dipanggil')->count(),
            'selesai' => \App\Models\Antrian::whereDate('waktu_antrian', $today)->where('status_antrian', 'selesai')->count(),
            'batal' => \App\Models\Antrian::whereDate('waktu_antrian', $today)->where('status_antrian', 'batal')->count(),
        ]);
    });
    
    // Helper API for next loket number
    Route::get('/next-loket-number', function () {
        $lastLoket = \App\Models\Loket::orderBy('id_loket', 'desc')->first();
        $nextNumber = $lastLoket ? ($lastLoket->id_loket + 1) : 1;
        return response()->json(['next_number' => $nextNumber]);
    });
});

// Admin API Routes (authenticated)
Route::prefix('api')->middleware(['auth.admin'])->group(function () {
    Route::get('/admin/antrian/today', function () {
        $antrians = \App\Models\Antrian::with(['pengunjung', 'layanan', 'admin'])
            ->whereDate('waktu_antrian', \Carbon\Carbon::today())
            ->orderBy('waktu_antrian', 'desc')
            ->get();
        return response()->json($antrians);
    });
});

Route::prefix('api')->group(function () {
    
    // Public API - no authentication required
    Route::get('/visitor/check', function(\Illuminate\Http\Request $request) {
        $nik = $request->get('nik');
        if ($nik) {
            $pengunjung = \App\Models\Pengunjung::where('nik', $nik)->first();
            if ($pengunjung) {
                return response()->json([
                    'exists' => true,
                    'visitor' => [
                        'nama_pengunjung' => $pengunjung->nama_pengunjung,
                        'no_hp' => $pengunjung->no_hp,
                        'waktu_daftar' => $pengunjung->waktu_daftar->format('d/m/Y')
                    ]
                ]);
            }
        }
        return response()->json(['exists' => false]);
    })->name('api.visitor.check');

    Route::get('/layanan/today-count', function(\Illuminate\Http\Request $request) {
        $layananId = $request->get('layanan_id');
        $count = 0;
        
        if ($layananId) {
            $count = \App\Models\Antrian::where('id_layanan', $layananId)
                ->whereDate('waktu_antrian', \Carbon\Carbon::today())
                ->count();
        }
        
        return response()->json(['count' => $count]);
    })->name('api.layanan.today-count');

    Route::get('/stats/today', function () {
        $today = \Carbon\Carbon::today();
        return response()->json([
            'total_antrian' => \App\Models\Antrian::whereDate('waktu_antrian', $today)->count(),
            'menunggu' => \App\Models\Antrian::whereDate('waktu_antrian', $today)->where('status_antrian', 'menunggu')->count(),
            'dipanggil' => \App\Models\Antrian::whereDate('waktu_antrian', $today)->where('status_antrian', 'dipanggil')->count(),
            'selesai' => \App\Models\Antrian::whereDate('waktu_antrian', $today)->where('status_antrian', 'selesai')->count(),
            'batal' => \App\Models\Antrian::whereDate('waktu_antrian', $today)->where('status_antrian', 'batal')->count(),
        ]);
    })->name('api.stats.today');

});

// API Routes khusus admin (authenticated)
Route::prefix('api')->middleware(['auth.admin'])->group(function () {
    
    Route::post('/antrian/{antrian}/send-notification', function($id) {
        try {
            $antrian = \App\Models\Antrian::with(['pengunjung', 'layanan'])->findOrFail($id);
            
            // Implementasi notifikasi (WhatsApp, SMS, dll)
            // Untuk sekarang return success
            
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi berhasil dikirim'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim notifikasi'
            ], 500);
        }
    })->name('api.antrian.send-notification');

    Route::get('/antrian/{antrian}/check-status', function($id) {
        try {
            $antrian = \App\Models\Antrian::findOrFail($id);
            return response()->json(['status' => $antrian->status_antrian]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Antrian tidak ditemukan'], 404);
        }
    })->name('api.antrian.check-status');

});

// Route untuk display antrian real-time
Route::get('/api/antrian/current-queue', [AntrianController::class, 'getCurrentQueue'])->name('antrian.current');