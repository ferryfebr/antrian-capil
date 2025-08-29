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
            // Basic statistics dengan error handling
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

            // Log untuk debugging dengan lebih aman
            try {
                Log::info('Dashboard data loaded successfully:', [
                    'total_layanan' => Layanan::count(),
                    'layanan_aktif' => Layanan::where('aktif', true)->count(),
                    'layanan_populer_count' => $layanan_populer->count(),
                    'stats' => $stats
                ]);
            } catch (\Exception $logError) {
                // Jika logging error, lanjutkan tanpa log
            }

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
            Log::error('Dashboard error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            // Return dashboard dengan data fallback
            return view('dashboard.index', [
                'stats' => $this->getMinimalStats(),
                'growth' => ['total_growth' => 0, 'completed_growth' => 0],
                'currentQueue' => null,
                'antrian_terbaru' => collect(),
                'layanan_populer' => collect(),
                'service_efficiency' => collect(),
                'active_lokets' => collect(),
                'hourly_data' => []
            ])->with('error', 'Terjadi kesalahan saat memuat dashboard. Beberapa data mungkin tidak tersedia.');
        }
    }

    private function getTodayStats()
    {
        try {
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
        } catch (\Exception $e) {
            Log::warning('Error getting today stats: ' . $e->getMessage());
            return $this->getMinimalStats();
        }
    }

    private function getGrowthData()
    {
        try {
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
        } catch (\Exception $e) {
            Log::warning('Error getting growth data: ' . $e->getMessage());
            return ['total_growth' => 0, 'completed_growth' => 0];
        }
    }

    private function getCurrentQueue()
    {
        try {
            return Antrian::with(['pengunjung', 'layanan'])
                ->where('status_antrian', 'dipanggil')
                ->whereDate('waktu_antrian', Carbon::today())
                ->latest('waktu_dipanggil')
                ->first();
        } catch (\Exception $e) {
            Log::warning('Error getting current queue: ' . $e->getMessage());
            return null;
        }
    }

    private function getRecentActivities()
    {
        try {
            return Antrian::with(['pengunjung', 'layanan', 'admin'])
                ->whereDate('waktu_antrian', Carbon::today())
                ->orderBy('waktu_antrian', 'desc')
                ->limit(10)
                ->get();
        } catch (\Exception $e) {
            Log::warning('Error getting recent activities: ' . $e->getMessage());
            return collect();
        }
    }

    private function getPopularServices()
    {
        try {
            // PERBAIKAN: Lebih defensive dengan null checking
            $services = Layanan::withCount(['antrian' => function($query) {
                    $query->whereDate('waktu_antrian', Carbon::today());
                }])
                ->where('aktif', true)
                ->having('antrian_count', '>', 0)
                ->orderBy('antrian_count', 'desc')
                ->limit(5)
                ->get();

            // Pastikan data tidak corrupt
            return $services->filter(function($layanan) {
                return $layanan && $layanan->nama_layanan && $layanan->kode_layanan;
            });

        } catch (\Exception $e) {
            Log::warning('Error getting popular services: ' . $e->getMessage());
            return collect();
        }
    }

    private function getServiceEfficiency()
    {
        try {
            $services = Layanan::with(['antrian' => function($query) {
                    $query->whereDate('waktu_antrian', Carbon::today());
                }])
                ->where('aktif', true)
                ->get()
                ->map(function($layanan) {
                    try {
                        // Defensive programming - check if antrian exists
                        $antrianCollection = $layanan->antrian ?? collect();
                        $totalToday = $antrianCollection->count();
                        $completedToday = $antrianCollection->where('status_antrian', 'selesai')->count();
                        
                        return [
                            'layanan' => $layanan,
                            'total_today' => $totalToday,
                            'completed_today' => $completedToday,
                            'utilization_rate' => ($layanan->kapasitas_harian ?? 1) > 0 
                                ? round(($totalToday / $layanan->kapasitas_harian) * 100, 1)
                                : 0
                        ];
                    } catch (\Exception $e) {
                        Log::warning('Error processing service efficiency for layanan ID: ' . ($layanan->id_layanan ?? 'unknown'));
                        return [
                            'layanan' => $layanan,
                            'total_today' => 0,
                            'completed_today' => 0,
                            'utilization_rate' => 0
                        ];
                    }
                })
                ->sortByDesc('utilization_rate')
                ->values();

            return $services;

        } catch (\Exception $e) {
            Log::warning('Error getting service efficiency: ' . $e->getMessage());
            return collect();
        }
    }

    private function getActiveLokets()
    {
        try {
            return Loket::with('layanan')
                ->where('status_loket', 'aktif')
                ->orderBy('nama_loket')
                ->get();
        } catch (\Exception $e) {
            Log::warning('Error getting active lokets: ' . $e->getMessage());
            return collect();
        }
    }

    private function getHourlyData()
    {
        try {
            $today = Carbon::today();
            $data = [];

            for ($hour = 8; $hour <= 17; $hour++) {
                try {
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
                } catch (\Exception $e) {
                    // Skip jam ini jika error
                    Log::warning("Error getting hourly data for hour {$hour}: " . $e->getMessage());
                    $data[] = [
                        'hour' => sprintf('%02d:00', $hour),
                        'total' => 0,
                        'completed' => 0
                    ];
                }
            }

            return $data;
        } catch (\Exception $e) {
            Log::warning('Error getting hourly data: ' . $e->getMessage());
            return [];
        }
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
                'success' => true,
                'stats' => $stats,
                'current_queue' => $currentQueue
            ]);

        } catch (\Exception $e) {
            Log::error('Realtime stats error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'error' => 'Error loading realtime stats',
                'stats' => $this->getMinimalStats(),
                'current_queue' => null
            ], 500);
        }
    }

    public function getQueueActivity()
    {
        try {
            $activities = $this->getRecentActivities();
            return response()->json([
                'success' => true,
                'activities' => $activities
            ]);

        } catch (\Exception $e) {
            Log::error('Queue activity error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error loading queue activities',
                'activities' => []
            ], 500);
        }
    }

    public function getServiceStats()
    {
        try {
            $layanan_populer = $this->getPopularServices();
            $service_efficiency = $this->getServiceEfficiency();

            return response()->json([
                'success' => true,
                'popular_services' => $layanan_populer,
                'service_efficiency' => $service_efficiency
            ]);

        } catch (\Exception $e) {
            Log::error('Service stats error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Error loading service stats',
                'popular_services' => [],
                'service_efficiency' => []
            ], 500);
        }
    }

    public function exportStats()
    {
        try {
            // Export functionality
            return response()->json([
                'success' => true, 
                'message' => 'Export feature coming soon'
            ]);
        } catch (\Exception $e) {
            Log::error('Export stats error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => 'Export failed'
            ], 500);
        }
    }
}