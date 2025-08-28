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

// Protected Routes (Admin Only)
Route::middleware(['auth:admin'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Admin Management
    Route::resource('admin', AdminController::class);
    
    // Antrian Management
    Route::resource('antrian', AntrianController::class);
    Route::patch('antrian/{antrian}/update-status', [AntrianController::class, 'updateStatus'])->name('antrian.update-status');
    
    // Layanan Management
    Route::resource('layanan', LayananController::class);
    Route::patch('layanan/{layanan}/toggle-status', [LayananController::class, 'toggleStatus'])->name('layanan.toggle-status');
    
    // Loket Management
    Route::resource('loket', LoketController::class);
    
    // Pengunjung Management
    Route::resource('pengunjung', PengunjungController::class);
    
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
});

// Admin API Routes (authenticated)
Route::prefix('api')->middleware(['auth:admin'])->group(function () {
    Route::get('/admin/antrian/today', function () {
        $antrians = \App\Models\Antrian::with(['pengunjung', 'layanan', 'admin'])
            ->whereDate('waktu_antrian', \Carbon\Carbon::today())
            ->orderBy('waktu_antrian', 'desc')
            ->get();
        return response()->json($antrians);
    });
});