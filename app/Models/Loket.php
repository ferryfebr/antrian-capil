<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loket extends Model
{
    use HasFactory;

    protected $table = 'loket';
    protected $primaryKey = 'id_loket';

    protected $fillable = [
        'nama_loket',
        'status_loket',
        'deskripsi_loket',
        'id_layanan',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with Layanan
     * Loket melayani satu jenis layanan (optional)
     */
    public function layanan()
    {
        return $this->belongsTo(Layanan::class, 'id_layanan', 'id_layanan');
    }

    /**
     * Scope untuk loket aktif
     */
    public function scopeAktif($query)
    {
        return $query->where('status_loket', 'aktif');
    }

    /**
     * Scope untuk loket tidak aktif
     */
    public function scopeTidakAktif($query)
    {
        return $query->where('status_loket', 'tidak_aktif');
    }

    /**
     * Scope untuk loket dengan layanan tertentu
     */
    public function scopeByLayanan($query, $layananId)
    {
        return $query->where('id_layanan', $layananId);
    }

    /**
     * Check if loket is active
     */
    public function isActive()
    {
        return $this->status_loket === 'aktif';
    }

    /**
     * Get loket display name
     */
    public function getDisplayNameAttribute()
    {
        $display = $this->nama_loket;
        
        if ($this->layanan) {
            $display .= ' - ' . $this->layanan->nama_layanan;
        }
        
        return $display;
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClassAttribute()
    {
        return $this->status_loket === 'aktif' ? 'bg-success' : 'bg-danger';
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->status_loket));
    }

    /**
     * Check if loket can be deleted
     */
    public function canBeDeleted()
    {
        // Can always be deleted - no direct relationships that prevent deletion
        return true;
    }

    /**
     * Get loket efficiency info
     */
    public function getEfficiencyInfoAttribute()
    {
        if (!$this->layanan) {
            return [
                'can_serve_all' => true,
                'specialization' => 'Semua Layanan'
            ];
        }

        return [
            'can_serve_all' => false,
            'specialization' => $this->layanan->nama_layanan,
            'estimated_duration' => $this->layanan->estimasi_durasi_layanan,
            'capacity_per_day' => $this->layanan->kapasitas_harian
        ];
    }

    /**
     * Toggle loket status
     */
    public function toggleStatus()
    {
        $this->status_loket = $this->status_loket === 'aktif' ? 'tidak_aktif' : 'aktif';
        return $this->save();
    }

    /**
     * Activate loket
     */
    public function activate()
    {
        $this->status_loket = 'aktif';
        return $this->save();
    }

    /**
     * Deactivate loket
     */
    public function deactivate()
    {
        $this->status_loket = 'tidak_aktif';
        return $this->save();
    }

    /**
     * Assign layanan to loket
     */
    public function assignLayanan($layananId)
    {
        $this->id_layanan = $layananId;
        return $this->save();
    }

    /**
     * Remove layanan assignment
     */
    public function removeLayanan()
    {
        $this->id_layanan = null;
        return $this->save();
    }

    /**
     * Get loket workload for today
     */
    public function getTodayWorkloadAttribute()
    {
        if (!$this->layanan) {
            return null;
        }

        $today = today();
        $antrianCount = $this->layanan->antrian()
            ->whereDate('waktu_antrian', $today)
            ->whereIn('status_antrian', ['selesai'])
            ->count();

        $estimatedTime = $antrianCount * $this->layanan->estimasi_durasi_layanan;

        return [
            'antrian_dilayani' => $antrianCount,
            'waktu_operasi_menit' => $estimatedTime,
            'waktu_operasi_jam' => round($estimatedTime / 60, 2),
            'utilization_rate' => $this->layanan->kapasitas_harian > 0 
                ? ($antrianCount / $this->layanan->kapasitas_harian) * 100 
                : 0
        ];
    }
}