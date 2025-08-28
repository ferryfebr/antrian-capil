<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    use HasFactory;

    protected $table = 'layanan';
    protected $primaryKey = 'id_layanan';

    protected $fillable = [
        'nama_layanan',
        'kode_layanan',
        'estimasi_durasi_layanan',
        'kapasitas_harian',
        'aktif',
        'id_admin',
    ];

    protected $casts = [
        'aktif' => 'boolean',
        'estimasi_durasi_layanan' => 'integer',
        'kapasitas_harian' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with Admin
     * Layanan dimiliki oleh satu admin
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    /**
     * Relationship with Loket
     * Layanan dapat dilayani di banyak loket
     */
    public function loket()
    {
        return $this->hasMany(Loket::class, 'id_layanan', 'id_layanan');
    }

    /**
     * Relationship with Antrian
     * Layanan dapat memiliki banyak antrian
     */
    public function antrian()
    {
        return $this->hasMany(Antrian::class, 'id_layanan', 'id_layanan');
    }

    /**
     * Scope untuk layanan aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    /**
     * Scope untuk layanan tidak aktif
     */
    public function scopeTidakAktif($query)
    {
        return $query->where('aktif', false);
    }

    /**
     * Get layanan display name with code
     */
    public function getDisplayNameAttribute()
    {
        return $this->nama_layanan . ' (' . $this->kode_layanan . ')';
    }

    /**
     * Get total working hours needed per day
     */
    public function getTotalJamOperasiAttribute()
    {
        return ($this->estimasi_durasi_layanan * $this->kapasitas_harian) / 60;
    }

    /**
     * Get efficiency in work days (assuming 8 hours per day)
     */
    public function getEfisiensiHariKerjaAttribute()
    {
        return $this->total_jam_operasi / 8;
    }

    /**
     * Get antrian per hour capacity
     */
    public function getAntrianPerJamAttribute()
    {
        return $this->estimasi_durasi_layanan > 0 ? 60 / $this->estimasi_durasi_layanan : 0;
    }

    /**
     * Check if layanan can be deleted
     */
    public function canBeDeleted()
    {
        // Cannot delete if has active queue
        $hasActiveQueue = $this->antrian()
            ->whereIn('status_antrian', ['menunggu', 'dipanggil'])
            ->exists();
        
        // Cannot delete if has loket
        $hasLoket = $this->loket()->exists();

        return !$hasActiveQueue && !$hasLoket;
    }

    /**
     * Get today's statistics
     */
    public function getTodayStatsAttribute()
    {
        $today = today();
        
        return [
            'total_antrian' => $this->antrian()->whereDate('waktu_antrian', $today)->count(),
            'menunggu' => $this->antrian()->whereDate('waktu_antrian', $today)->where('status_antrian', 'menunggu')->count(),
            'dipanggil' => $this->antrian()->whereDate('waktu_antrian', $today)->where('status_antrian', 'dipanggil')->count(),
            'selesai' => $this->antrian()->whereDate('waktu_antrian', $today)->where('status_antrian', 'selesai')->count(),
            'batal' => $this->antrian()->whereDate('waktu_antrian', $today)->where('status_antrian', 'batal')->count(),
        ];
    }

    /**
     * Get utilization rate for today
     */
    public function getTodayUtilizationAttribute()
    {
        $todayTotal = $this->antrian()->whereDate('waktu_antrian', today())->count();
        return $this->kapasitas_harian > 0 ? ($todayTotal / $this->kapasitas_harian) * 100 : 0;
    }

    /**
     * Get completion rate for today
     */
    public function getTodayCompletionRateAttribute()
    {
        $stats = $this->today_stats;
        return $stats['total_antrian'] > 0 ? ($stats['selesai'] / $stats['total_antrian']) * 100 : 0;
    }

    /**
     * Get next queue number for this service
     */
    public function getNextQueueNumber()
    {
        $today = today();
        $lastNumber = $this->antrian()
            ->whereDate('waktu_antrian', $today)
            ->count();
        
        return $this->kode_layanan . '-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate estimated waiting time
     */
    public function calculateEstimatedWaitTime()
    {
        $today = today();
        $waitingCount = $this->antrian()
            ->whereDate('waktu_antrian', $today)
            ->where('status_antrian', 'menunggu')
            ->count();
        
        return now()->addMinutes($waitingCount * $this->estimasi_durasi_layanan);
    }
}