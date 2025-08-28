<?php
// File: config/queue-system.php
// Custom configuration untuk sistem antrian

return [
    
    /*
    |--------------------------------------------------------------------------
    | Queue System Configuration
    |--------------------------------------------------------------------------
    */
    
    'system_name' => env('SYSTEM_NAME', 'Sistem Antrian Online Disdukcapil'),
    'office_name' => env('OFFICE_NAME', 'Dinas Kependudukan dan Pencatatan Sipil'),
    'office_address' => env('OFFICE_ADDRESS', 'Jl. Malioboro No. 1, Yogyakarta'),
    'office_phone' => env('OFFICE_PHONE', '(0274) 123456'),
    'office_hours' => env('OFFICE_HOURS', 'Senin - Jumat: 08:00 - 15:00'),
    
    /*
    |--------------------------------------------------------------------------
    | Queue Settings
    |--------------------------------------------------------------------------
    */
    
    'auto_refresh_interval' => env('AUTO_REFRESH_INTERVAL', 5000), // milliseconds
    'queue_display_refresh' => env('QUEUE_DISPLAY_REFRESH', 3000), // milliseconds
    'max_daily_queue' => env('MAX_DAILY_QUEUE', 500),
    
    /*
    |--------------------------------------------------------------------------
    | Default Queue Durations (minutes)
    |--------------------------------------------------------------------------
    */
    
    'default_durations' => [
        'KTP' => 30,
        'KK' => 25, 
        'KIA' => 20,
        'AKTA' => 40,
        'KAWIN' => 45,
        'MATI' => 35,
        'PINDAH' => 30,
        'LEGAL' => 15,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Default Daily Capacities
    |--------------------------------------------------------------------------
    */
    
    'default_capacities' => [
        'KTP' => 40,
        'KK' => 35,
        'KIA' => 30, 
        'AKTA' => 25,
        'KAWIN' => 20,
        'MATI' => 15,
        'PINDAH' => 20,
        'LEGAL' => 50,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    */
    
    'notifications' => [
        'whatsapp_enabled' => env('WHATSAPP_ENABLED', false),
        'sms_enabled' => env('SMS_ENABLED', false),
        'email_enabled' => env('EMAIL_ENABLED', true),
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Ticket Printer Settings
    |--------------------------------------------------------------------------
    */
    
    'printer' => [
        'enabled' => env('PRINTER_ENABLED', false),
        'printer_name' => env('PRINTER_NAME', 'Thermal Printer'),
        'paper_width' => env('PRINTER_PAPER_WIDTH', 80), // mm
        'logo_enabled' => env('PRINTER_LOGO_ENABLED', true),
    ],
    
];

// ============================================

// File: app/helpers.php
// Helper functions untuk sistem antrian

if (!function_exists('formatNik')) {
    /**
     * Format NIK dengan spasi untuk readability
     */
    function formatNik($nik) {
        if (strlen($nik) === 16) {
            return substr($nik, 0, 2) . ' ' . 
                   substr($nik, 2, 2) . ' ' . 
                   substr($nik, 4, 2) . ' ' . 
                   substr($nik, 6, 6) . ' ' . 
                   substr($nik, 12, 4);
        }
        return $nik;
    }
}

if (!function_exists('generateQueueNumber')) {
    /**
     * Generate nomor antrian
     */
    function generateQueueNumber($layananKode, $tanggal = null) {
        $tanggal = $tanggal ?: \Carbon\Carbon::today();
        
        $lastNumber = \App\Models\Antrian::whereDate('waktu_antrian', $tanggal)
            ->whereHas('layanan', function($q) use ($layananKode) {
                $q->where('kode_layanan', $layananKode);
            })
            ->count();
        
        return $layananKode . '-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('getQueuePosition')) {
    /**
     * Get posisi antrian saat ini
     */
    function getQueuePosition($antrianId) {
        $antrian = \App\Models\Antrian::find($antrianId);
        
        if (!$antrian || $antrian->status_antrian !== 'menunggu') {
            return null;
        }
        
        return \App\Models\Antrian::where('id_layanan', $antrian->id_layanan)
            ->whereDate('waktu_antrian', $antrian->waktu_antrian->toDateString())
            ->where('status_antrian', 'menunggu')
            ->where('waktu_antrian', '<', $antrian->waktu_antrian)
            ->count() + 1;
    }
}

if (!function_exists('getEstimatedWaitTime')) {
    /**
     * Hitung estimasi waktu tunggu
     */
    function getEstimatedWaitTime($layananId, $posisi = null) {
        $layanan = \App\Models\Layanan::find($layananId);
        if (!$layanan) return null;
        
        $posisi = $posisi ?: \App\Models\Antrian::where('id_layanan', $layananId)
            ->whereDate('waktu_antrian', \Carbon\Carbon::today())
            ->where('status_antrian', 'menunggu')
            ->count();
        
        $estimasiMenit = $posisi * $layanan->estimasi_durasi_layanan;
        
        return \Carbon\Carbon::now()->addMinutes($estimasiMenit);
    }
}

if (!function_exists('getWorkingHours')) {
    /**
     * Check apakah masih jam kerja
     */
    function getWorkingHours() {
        return [
            'start' => '08:00',
            'end' => '15:00',
            'days' => [1, 2, 3, 4, 5], // Monday to Friday
        ];
    }
}

if (!function_exists('isWorkingTime')) {
    /**
     * Check apakah saat ini masih jam kerja
     */
    function isWorkingTime() {
        $now = \Carbon\Carbon::now();
        $hours = getWorkingHours();
        
        // Check day of week
        if (!in_array($now->dayOfWeek, $hours['days'])) {
            return false;
        }
        
        // Check time
        $startTime = \Carbon\Carbon::createFromTimeString($hours['start']);
        $endTime = \Carbon\Carbon::createFromTimeString($hours['end']);
        
        return $now->between($startTime, $endTime);
    }
}

if (!function_exists('formatPhoneNumber')) {
    /**
     * Format nomor telepon
     */
    function formatPhoneNumber($phone) {
        if (!$phone) return null;
        
        // Remove all non-numeric characters except +
        $cleaned = preg_replace('/[^\d+]/', '', $phone);
        
        // Convert 08xx to +628xx
        if (substr($cleaned, 0, 2) === '08') {
            $cleaned = '+62' . substr($cleaned, 1);
        }
        
        return $cleaned;
    }
}

if (!function_exists('getQueueStats')) {
    /**
     * Get statistik antrian untuk tanggal tertentu
     */
    function getQueueStats($date = null) {
        $date = $date ?: \Carbon\Carbon::today();
        
        return [
            'total' => \App\Models\Antrian::whereDate('waktu_antrian', $date)->count(),
            'menunggu' => \App\Models\Antrian::whereDate('waktu_antrian', $date)->where('status_antrian', 'menunggu')->count(),
            'dipanggil' => \App\Models\Antrian::whereDate('waktu_antrian', $date)->where('status_antrian', 'dipanggil')->count(), 
            'selesai' => \App\Models\Antrian::whereDate('waktu_antrian', $date)->where('status_antrian', 'selesai')->count(),
            'batal' => \App\Models\Antrian::whereDate('waktu_antrian', $date)->where('status_antrian', 'batal')->count(),
        ];
    }
}