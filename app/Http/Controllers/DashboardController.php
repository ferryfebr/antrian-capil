<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use App\Models\Layanan;
use App\Models\Admin;
use App\Models\Pengunjung;
use App\Models\Loket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Basic statistics
            $stats = $this->getTodayStats();
            
            // Growth data (comparison with yesterday)
            $growth = $this->getGrowthData();
            
            // Current queue being called
            $currentQueue = $this->getCurrentQueue();
            
            // Recent queue activities
            $antrian_terbaru = $this->getRecentActivities();
            
            // Popular services with today's queue count
            $layanan_populer = $this->getPopularServices();
            
            // Service efficiency data
            $service_efficiency = $this->getServiceEfficiency();
            
            // Active counters/lokets
            $active_lokets = $this->getActiveLokets();
            
            // Hourly data for charts
            $hourly_data = $this->getHourlyData();

            // Log untuk debugging
            Log::info('Dashboard data loaded:', [
                'total_layanan' => Layanan::count(),
                'layanan_aktif' => Layanan::where('aktif', true)->count(),
                'layanan_populer_count' => $layanan_populer->count()
            ]);

            return view('dashboard.index', compact(
                'stats',
                'growth',
                'currentQueue',
                'antrian_terbaru',
                'layanan_populer',
                'service_efficiency',
                'active_lokets',
                'hourly_data'
            ));

        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage());
            
            // Return dashboard with minimal data if error occurs
            return view('dashboard.index', [
                'stats' => $this->getMinimalStats(),
                'growth' => ['total_growth' => 0, 'completed_growth' => 0],
                'currentQueue' => null,
                'antrian_terbaru' => collect(),
                'layanan_populer' => collect(),
                'service_efficiency' => collect(),
                'active_lokets' => collect(),
                'hourly_data' => []
            ]);
        }
    }

    private function getTodayStats()
    {
        $today = Carbon::today();

        return [
            'total_antrian_hari_ini' => Antrian::whereDate('waktu_antrian', $today)->count(),
            'antrian_menunggu' => Antrian::whereDate('waktu_antrian', $today)
                ->where('status_antrian', 'menunggu')->count(),
            'antrian_dipanggil' => Antrian::whereDate('waktu_antrian', $today)
                ->where('status_antrian', 'dipanggil')->count(),
            'antrian_selesai' => Antrian::whereDate('waktu_antrian', $today)
                ->where('status_antrian', 'selesai')->count(),
        ];
    }

    private function getGrowthData()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        $todayTotal = Antrian::whereDate('waktu_antrian', $today)->count();
        $yesterdayTotal = Antrian::whereDate('waktu_antrian', $yesterday)->count();

        $todayCompleted = Antrian::whereDate('waktu_antrian', $today)
            ->where('status_antrian', 'selesai')->count();
        $yesterdayCompleted = Antrian::whereDate('waktu_antrian', $yesterday)
            ->where('status_antrian', 'selesai')->count();

        return [
            'total_growth' => $yesterdayTotal > 0 
                ? round((($todayTotal - $yesterdayTotal) / $yesterdayTotal) * 100, 1)
                : 0,
            'completed_growth' => $yesterdayCompleted > 0 
                ? round((($todayCompleted - $yesterdayCompleted) / $yesterdayCompleted) * 100, 1)
                : 0
        ];
    }

    private function getCurrentQueue()
    {
        return Antrian::with(['pengunjung', 'layanan'])
            ->where('status_antrian', 'dipanggil')
            ->whereDate('waktu_antrian', Carbon::today())
            ->latest('waktu_dipanggil')
            ->first();
    }

    private function getRecentActivities()
    {
        return Antrian::with(['pengunjung', 'layanan', 'admin'])
            ->whereDate('waktu_antrian', Carbon::today())
            ->orderBy('waktu_antrian', 'desc')
            ->limit(10)
            ->get();
    }

    private function getPopularServices()
    {
        // PERBAIKAN: Pastikan hanya mengambil layanan aktif
        return Layanan::withCount(['antrian' => function($query) {
                $query->whereDate('waktu_antrian', Carbon::today());
            }])
            ->where('aktif', true) // PENTING: Hanya layanan aktif
            ->having('antrian_count', '>', 0) // Hanya yang ada antriannya
            ->orderBy('antrian_count', 'desc')
            ->limit(5)
            ->get();
    }

    private function getServiceEfficiency()
    {
        // PERBAIKAN: Hanya layanan aktif
        return Layanan::with(['antrian' => function($query) {
                $query->whereDate('waktu_antrian', Carbon::today());
            }])
            ->where('aktif', true) // PENTING: Hanya layanan aktif
            ->get()
            ->map(function($layanan) {
                $totalToday = $layanan->antrian->count();
                $completedToday = $layanan->antrian->where('status_antrian', 'selesai')->count();
                
                return [
                    'layanan' => $layanan,
                    'total_today' => $totalToday,
                    'completed_today' => $completedToday,
                    'utilization_rate' => $layanan->kapasitas_harian > 0 
                        ? round(($totalToday / $layanan->kapasitas_harian) * 100, 1)
                        : 0
                ];
            })
            ->sortByDesc('utilization_rate')
            ->values();
    }

    private function getActiveLokets()
    {
        return Loket::with('layanan')
            ->where('status_loket', 'aktif')
            ->orderBy('nama_loket')
            ->get();
    }

    private function getHourlyData()
    {
        $today = Carbon::today();
        $data = [];

        for ($hour = 8; $hour <= 17; $hour++) {
            $startHour = $today->copy()->setHour($hour)->setMinute(0)->setSecond(0);
            $endHour = $startHour->copy()->addHour();

            $total = Antrian::whereBetween('waktu_antrian', [$startHour, $endHour])->count();
            $completed = Antrian::whereBetween('waktu_antrian', [$startHour, $endHour])
                ->where('status_antrian', 'selesai')->count();

            $data[] = [
                'hour' => sprintf('%02d:00', $hour),
                'total' => $total,
                'completed' => $completed
            ];
        }

        return $data;
    }

    private function getMinimalStats()
    {
        return [
            'total_antrian_hari_ini' => 0,
            'antrian_menunggu' => 0,
            'antrian_dipanggil' => 0,
            'antrian_selesai' => 0,
        ];
    }

    public function getRealtimeStats()
    {
        try {
            $stats = $this->getTodayStats();
            $currentQueue = $this->getCurrentQueue();

            return response()->json([
                'stats' => $stats,
                'current_queue' => $currentQueue
            ]);

        } catch (\Exception $e) {
            Log::error('Realtime stats error: ' . $e->getMessage());
            
            return response()->json([
                'stats' => $this->getMinimalStats(),
                'current_queue' => null
            ], 500);
        }
    }

    public function getQueueActivity()
    {
        try {
            $activities = $this->getRecentActivities();
            return response()->json($activities);

        } catch (\Exception $e) {
            Log::error('Queue activity error: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }

    public function getServiceStats()
    {
        try {
            $layanan_populer = $this->getPopularServices();
            $service_efficiency = $this->getServiceEfficiency();

            return response()->json([
                'popular_services' => $layanan_populer,
                'service_efficiency' => $service_efficiency
            ]);

        } catch (\Exception $e) {
            Log::error('Service stats error: ' . $e->getMessage());
            return response()->json([
                'popular_services' => [],
                'service_efficiency' => []
            ], 500);
        }
    }

    public function exportStats()
    {
        // Export functionality
        return response()->json(['message' => 'Export feature coming soon']);
    }
}