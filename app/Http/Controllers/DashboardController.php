<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Antrian;
use App\Models\Layanan;
use App\Models\Pengunjung;
use App\Models\Loket;
use App\Models\Admin;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display dashboard with statistics
     */
    public function index()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        
        // Basic statistics for today
        $stats = $this->getTodayStats($today);
        
        // Growth statistics (today vs yesterday)
        $growth = $this->getGrowthStats($today, $yesterday);
        
        // Recent queues
        $antrian_terbaru = Antrian::with(['pengunjung', 'layanan', 'admin'])
            ->whereDate('waktu_antrian', $today)
            ->orderBy('waktu_antrian', 'desc')
            ->limit(10)
            ->get();

        // Popular services today
        $layanan_populer = Layanan::withCount(['antrian' => function($query) use ($today) {
                $query->whereDate('waktu_antrian', $today);
            }])
            ->having('antrian_count', '>', 0)
            ->orderBy('antrian_count', 'desc')
            ->limit(5)
            ->get();

        // Queue status distribution
        $queue_distribution = $this->getQueueDistribution($today);
        
        // Hourly queue data for chart
        $hourly_data = $this->getHourlyQueueData($today);
        
        // Service efficiency data
        $service_efficiency = $this->getServiceEfficiencyData($today);
        
        // Active lokets
        $active_lokets = Loket::with('layanan')
            ->where('status_loket', 'aktif')
            ->get();

        return view('dashboard.index', compact(
            'stats',
            'growth', 
            'antrian_terbaru',
            'layanan_populer',
            'queue_distribution',
            'hourly_data',
            'service_efficiency',
            'active_lokets'
        ));
    }

    /**
     * Get today's basic statistics
     */
    private function getTodayStats($today)
    {
        return [
            'total_antrian_hari_ini' => Antrian::whereDate('waktu_antrian', $today)->count(),
            'antrian_menunggu' => Antrian::whereDate('waktu_antrian', $today)->where('status_antrian', 'menunggu')->count(),
            'antrian_dipanggil' => Antrian::whereDate('waktu_antrian', $today)->where('status_antrian', 'dipanggil')->count(),
            'antrian_selesai' => Antrian::whereDate('waktu_antrian', $today)->where('status_antrian', 'selesai')->count(),
            'antrian_batal' => Antrian::whereDate('waktu_antrian', $today)->where('status_antrian', 'batal')->count(),
            'total_pengunjung' => Pengunjung::count(),
            'pengunjung_baru_hari_ini' => Pengunjung::whereDate('waktu_daftar', $today)->count(),
            'total_layanan' => Layanan::where('aktif', true)->count(),
            'loket_aktif' => Loket::where('status_loket', 'aktif')->count(),
            'total_admin' => Admin::count()
        ];
    }

    /**
     * Get growth statistics compared to yesterday
     */
    private function getGrowthStats($today, $yesterday)
    {
        $todayTotal = Antrian::whereDate('waktu_antrian', $today)->count();
        $yesterdayTotal = Antrian::whereDate('waktu_antrian', $yesterday)->count();
        
        $todayCompleted = Antrian::whereDate('waktu_antrian', $today)->where('status_antrian', 'selesai')->count();
        $yesterdayCompleted = Antrian::whereDate('waktu_antrian', $yesterday)->where('status_antrian', 'selesai')->count();

        return [
            'total_growth' => $this->calculateGrowthPercentage($todayTotal, $yesterdayTotal),
            'completed_growth' => $this->calculateGrowthPercentage($todayCompleted, $yesterdayCompleted),
            'yesterday_total' => $yesterdayTotal,
            'yesterday_completed' => $yesterdayCompleted
        ];
    }

    /**
     * Calculate growth percentage
     */
    private function calculateGrowthPercentage($today, $yesterday)
    {
        if ($yesterday == 0) {
            return $today > 0 ? 100 : 0;
        }
        
        return round((($today - $yesterday) / $yesterday) * 100, 1);
    }

    /**
     * Get queue status distribution
     */
    private function getQueueDistribution($today)
    {
        return Antrian::whereDate('waktu_antrian', $today)
            ->select('status_antrian', DB::raw('count(*) as total'))
            ->groupBy('status_antrian')
            ->pluck('total', 'status_antrian')
            ->toArray();
    }

    /**
     * Get hourly queue data for charts
     */
    private function getHourlyQueueData($today)
    {
        $hourlyData = [];
        
        for ($hour = 8; $hour <= 16; $hour++) {
            $startTime = $today->copy()->setHour($hour)->setMinute(0)->setSecond(0);
            $endTime = $startTime->copy()->addHour();
            
            $count = Antrian::whereBetween('waktu_antrian', [$startTime, $endTime])->count();
            
            $hourlyData[] = [
                'hour' => $hour . ':00',
                'total' => $count,
                'completed' => Antrian::whereBetween('waktu_antrian', [$startTime, $endTime])
                    ->where('status_antrian', 'selesai')->count()
            ];
        }

        return $hourlyData;
    }

    /**
     * Get service efficiency data
     */
    private function getServiceEfficiencyData($today)
    {
        return Layanan::with(['antrian' => function($query) use ($today) {
                $query->whereDate('waktu_antrian', $today);
            }])
            ->get()
            ->map(function($layanan) {
                $totalToday = $layanan->antrian->count();
                $completedToday = $layanan->antrian->where('status_antrian', 'selesai')->count();
                $utilizationRate = $layanan->kapasitas_harian > 0 ? 
                    ($totalToday / $layanan->kapasitas_harian) * 100 : 0;
                $completionRate = $totalToday > 0 ? 
                    ($completedToday / $totalToday) * 100 : 0;

                return [
                    'layanan' => $layanan,
                    'total_today' => $totalToday,
                    'completed_today' => $completedToday,
                    'utilization_rate' => round($utilizationRate, 1),
                    'completion_rate' => round($completionRate, 1)
                ];
            })
            ->sortByDesc('total_today');
    }

    /**
     * Get real-time stats for AJAX updates
     */
    public function getRealtimeStats()
    {
        $today = Carbon::today();
        $stats = $this->getTodayStats($today);
        
        // Add current queue being called
        $currentQueue = Antrian::with(['pengunjung', 'layanan'])
            ->whereDate('waktu_antrian', $today)
            ->where('status_antrian', 'dipanggil')
            ->latest('waktu_dipanggil')
            ->first();

        return response()->json([
            'stats' => $stats,
            'current_queue' => $currentQueue,
            'timestamp' => now()->format('H:i:s')
        ]);
    }

    /**
     * Get queue activity for real-time updates
     */
    public function getQueueActivity()
    {
        $recentActivity = Antrian::with(['pengunjung', 'layanan', 'admin'])
            ->whereDate('waktu_antrian', today())
            ->where('updated_at', '>=', now()->subMinutes(5))
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json($recentActivity);
    }

    /**
     * Get service statistics
     */
    public function getServiceStats()
    {
        $today = Carbon::today();
        
        $serviceStats = Layanan::withCount([
            'antrian as today_total' => function($query) use ($today) {
                $query->whereDate('waktu_antrian', $today);
            },
            'antrian as today_completed' => function($query) use ($today) {
                $query->whereDate('waktu_antrian', $today)
                      ->where('status_antrian', 'selesai');
            },
            'antrian as today_waiting' => function($query) use ($today) {
                $query->whereDate('waktu_antrian', $today)
                      ->where('status_antrian', 'menunggu');
            }
        ])
        ->where('aktif', true)
        ->get()
        ->map(function($layanan) {
            return [
                'id' => $layanan->id_layanan,
                'nama' => $layanan->nama_layanan,
                'kode' => $layanan->kode_layanan,
                'today_total' => $layanan->today_total,
                'today_completed' => $layanan->today_completed,
                'today_waiting' => $layanan->today_waiting,
                'capacity' => $layanan->kapasitas_harian,
                'utilization' => $layanan->kapasitas_harian > 0 ? 
                    round(($layanan->today_total / $layanan->kapasitas_harian) * 100, 1) : 0
            ];
        });

        return response()->json($serviceStats);
    }

    /**
     * Export dashboard data to CSV
     */
    public function exportStats(Request $request)
    {
        $date = $request->get('date', today()->toDateString());
        $targetDate = Carbon::parse($date);
        
        $stats = $this->getTodayStats($targetDate);
        $distribution = $this->getQueueDistribution($targetDate);
        $hourlyData = $this->getHourlyQueueData($targetDate);
        
        $filename = 'dashboard_stats_' . $targetDate->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($stats, $distribution, $hourlyData, $targetDate) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['Dashboard Statistics - ' . $targetDate->format('d/m/Y')]);
            fputcsv($file, []);
            
            // Basic stats
            fputcsv($file, ['Basic Statistics']);
            fputcsv($file, ['Metric', 'Value']);
            fputcsv($file, ['Total Antrian', $stats['total_antrian_hari_ini']]);
            fputcsv($file, ['Menunggu', $stats['antrian_menunggu']]);
            fputcsv($file, ['Selesai', $stats['antrian_selesai']]);
            fputcsv($file, ['Batal', $stats['antrian_batal']]);
            fputcsv($file, []);
            
            // Status distribution
            fputcsv($file, ['Status Distribution']);
            fputcsv($file, ['Status', 'Count']);
            foreach ($distribution as $status => $count) {
                fputcsv($file, [ucfirst($status), $count]);
            }
            fputcsv($file, []);
            
            // Hourly data
            fputcsv($file, ['Hourly Distribution']);
            fputcsv($file, ['Hour', 'Total', 'Completed']);
            foreach ($hourlyData as $hour) {
                fputcsv($file, [$hour['hour'], $hour['total'], $hour['completed']]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}