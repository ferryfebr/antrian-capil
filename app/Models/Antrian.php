<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Antrian extends Model
{
    use HasFactory;

    protected $table = 'antrian';
    protected $primaryKey = 'id_antrian';

    protected $fillable = [
        'nomor_antrian',
        'id_pengunjung',
        'id_layanan',
        'waktu_antrian',
        'waktu_estimasi',
        'status_antrian'
    ];

    protected $dates = [
        'waktu_antrian',
        'waktu_estimasi',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'waktu_antrian' => 'datetime',
        'waktu_estimasi' => 'datetime'
    ];

    /**
     * Available status options
     */
    const STATUS_MENUNGGU = 'menunggu';
    const STATUS_DIPANGGIL = 'dipanggil';
    const STATUS_SELESAI = 'selesai';
    const STATUS_BATAL = 'batal';

    /**
     * Get all available status options
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_MENUNGGU => 'Menunggu',
            self::STATUS_DIPANGGIL => 'Dipanggil',
            self::STATUS_SELESAI => 'Selesai',
            self::STATUS_BATAL => 'Batal',
        ];
    }

    /**
     * Relationship with Admin
     * Antrian diproses oleh admin
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    /**
     * Relationship with Pengunjung
     * Antrian dimiliki oleh pengunjung
     */
    public function pengunjung()
    {
        return $this->belongsTo(Pengunjung::class, 'id_pengunjung', 'id_pengunjung');
    }

    /**
     * Relationship with Layanan
     * Antrian untuk satu jenis layanan
     */
    public function layanan()
    {
        return $this->belongsTo(Layanan::class, 'id_layanan', 'id_layanan');
    }

    /**
     * Scope untuk antrian menunggu
     */
    public function scopeMenunggu($query)
    {
        return $query->where('status_antrian', self::STATUS_MENUNGGU);
    }

    /**
     * Scope untuk antrian dipanggil
     */
    public function scopeDipanggil($query)
    {
        return $query->where('status_antrian', self::STATUS_DIPANGGIL);
    }

    /**
     * Scope untuk antrian selesai
     */
    public function scopeSelesai($query)
    {
        return $query->where('status_antrian', self::STATUS_SELESAI);
    }

    /**
     * Scope untuk antrian batal
     */
    public function scopeBatal($query)
    {
        return $query->where('status_antrian', self::STATUS_BATAL);
    }

    /**
     * Scope untuk antrian aktif (menunggu atau dipanggil)
     */
    public function scopeAktif($query)
    {
        return $query->whereIn('status_antrian', [self::STATUS_MENUNGGU, self::STATUS_DIPANGGIL]);
    }

    /**
     * Scope untuk antrian hari ini
     */
    public function scopeHariIni($query)
    {
        return $query->whereDate('waktu_antrian', Carbon::today());
    }

    /**
     * Scope untuk antrian pada tanggal tertentu
     */
    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('waktu_antrian', $date);
    }

    /**
     * Scope untuk antrian berdasarkan layanan
     */
    public function scopeByLayanan($query, $layananId)
    {
        return $query->where('id_layanan', $layananId);
    }

    /**
     * Check if antrian is active
     */
    public function isActive()
    {
        return in_array($this->status_antrian, [self::STATUS_MENUNGGU, self::STATUS_DIPANGGIL]);
    }

    /**
     * Check if antrian is completed
     */
    public function isCompleted()
    {
        return in_array($this->status_antrian, [self::STATUS_SELESAI, self::STATUS_BATAL]);
    }

    /**
     * Get status badge class for UI
     */
    public function getStatusBadgeClassAttribute()
    {
        switch ($this->status_antrian) {
            case self::STATUS_MENUNGGU:
                return 'bg-warning text-dark';
            case self::STATUS_DIPANGGIL:
                return 'bg-primary';
            case self::STATUS_SELESAI:
                return 'bg-success';
            case self::STATUS_BATAL:
                return 'bg-danger';
            default:
                return 'bg-secondary';
        }
    }

    /**
     * Get status text with icon
     */
    public function getStatusTextWithIconAttribute()
    {
        $icons = [
            self::STATUS_MENUNGGU => 'fas fa-clock',
            self::STATUS_DIPANGGIL => 'fas fa-volume-up',
            self::STATUS_SELESAI => 'fas fa-check',
            self::STATUS_BATAL => 'fas fa-times',
        ];

        $texts = self::getStatusOptions();
        $icon = $icons[$this->status_antrian] ?? 'fas fa-question';
        $text = $texts[$this->status_antrian] ?? 'Unknown';

        return "<i class='{$icon} me-1'></i>{$text}";
    }

    /**
     * Get formatted waktu antrian
     */
    public function getWaktuAntrianFormattedAttribute()
    {
        return $this->waktu_antrian ? $this->waktu_antrian->format('d/m/Y H:i:s') : null;
    }

    /**
     * Get formatted waktu estimasi
     */
    public function getWaktuEstimasiFormattedAttribute()
    {
        return $this->waktu_estimasi ? $this->waktu_estimasi->format('H:i') : null;
    }

    /**
     * Get formatted waktu dipanggil
     */
    public function getWaktuDipanggilFormattedAttribute()
    {
        return $this->waktu_dipanggil ? $this->waktu_dipanggil->format('d/m/Y H:i:s') : null;
    }

    /**
     * Calculate waiting time in minutes
     */
    public function getWaitingTimeAttribute()
    {
        if (!$this->waktu_dipanggil || !$this->waktu_antrian) {
            return null;
        }

        return $this->waktu_antrian->diffInMinutes($this->waktu_dipanggil);
    }

    /**
     * Calculate service time in minutes
     */
    public function getServiceTimeAttribute()
    {
        if (!$this->waktu_dipanggil || $this->status_antrian !== self::STATUS_SELESAI) {
            return null;
        }

        return $this->waktu_dipanggil->diffInMinutes($this->updated_at);
    }

    /**
     * Get queue position for waiting queues
     */
    public function getQueuePositionAttribute()
    {
        if ($this->status_antrian !== self::STATUS_MENUNGGU) {
            return null;
        }

        return self::where('id_layanan', $this->id_layanan)
            ->whereDate('waktu_antrian', $this->waktu_antrian->toDateString())
            ->where('status_antrian', self::STATUS_MENUNGGU)
            ->where('waktu_antrian', '<', $this->waktu_antrian)
            ->count() + 1;
    }

    /**
     * Update status to dipanggil
     */
    public function callQueue($adminId = null)
    {
        $this->status_antrian = self::STATUS_DIPANGGIL;
        $this->waktu_dipanggil = now();
        if ($adminId) {
            $this->id_admin = $adminId;
        }
        
        return $this->save();
    }

    /**
     * Update status to selesai
     */
    public function completeQueue($adminId = null)
    {
        $this->status_antrian = self::STATUS_SELESAI;
        if ($adminId) {
            $this->id_admin = $adminId;
        }
        
        return $this->save();
    }

    /**
     * Update status to batal
     */
    public function cancelQueue($adminId = null)
    {
        $this->status_antrian = self::STATUS_BATAL;
        if ($adminId) {
            $this->id_admin = $adminId;
        }
        
        return $this->save();
    }

    /**
     * Reset to menunggu status
     */
    public function resetQueue()
    {
        $this->status_antrian = self::STATUS_MENUNGGU;
        $this->waktu_dipanggil = null;
        $this->id_admin = null;
        
        return $this->save();
    }

    /**
     * Generate queue number for this service
     */
    public static function generateQueueNumber($layananId, $date = null)
    {
        $date = $date ?: today();
        $layanan = \App\Models\Layanan::find($layananId);
        
        if (!$layanan) {
            throw new \Exception('Layanan tidak ditemukan');
        }

        $lastNumber = self::whereDate('waktu_antrian', $date)
            ->where('id_layanan', $layananId)
            ->count();

        return $layanan->kode_layanan . '-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Calculate estimated waiting time
     */
    public function calculateEstimatedTime()
    {
        if ($this->status_antrian !== self::STATUS_MENUNGGU) {
            return null;
        }

        $queuesBefore = self::where('id_layanan', $this->id_layanan)
            ->whereDate('waktu_antrian', $this->waktu_antrian->toDateString())
            ->where('status_antrian', self::STATUS_MENUNGGU)
            ->where('waktu_antrian', '<', $this->waktu_antrian)
            ->count();

        $estimatedMinutes = $queuesBefore * $this->layanan->estimasi_durasi_layanan;
        
        return now()->addMinutes($estimatedMinutes);
    }

    /**
     * Get time until estimated call
     */
    public function getTimeUntilCallAttribute()
    {
        if (!$this->waktu_estimasi || $this->status_antrian !== self::STATUS_MENUNGGU) {
            return null;
        }

        $now = now();
        
        if ($this->waktu_estimasi <= $now) {
            return 'Segera dipanggil';
        }

        $minutes = $now->diffInMinutes($this->waktu_estimasi);
        
        if ($minutes < 60) {
            return "{$minutes} menit lagi";
        }
        
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;
        
        return "{$hours} jam {$remainingMinutes} menit lagi";
    }

    /**
     * Check if queue can be updated to specific status
     */
    public function canUpdateToStatus($newStatus)
    {
        $allowedTransitions = [
            self::STATUS_MENUNGGU => [self::STATUS_DIPANGGIL, self::STATUS_BATAL],
            self::STATUS_DIPANGGIL => [self::STATUS_SELESAI, self::STATUS_BATAL, self::STATUS_MENUNGGU],
            self::STATUS_SELESAI => [], // Final state
            self::STATUS_BATAL => [self::STATUS_MENUNGGU], // Can be reactivated
        ];

        return in_array($newStatus, $allowedTransitions[$this->status_antrian] ?? []);
    }

    /**
     * Get summary text for display
     */
    public function getSummaryAttribute()
    {
        return "{$this->nomor_antrian} - {$this->pengunjung->nama_pengunjung} - {$this->layanan->nama_layanan}";
    }

    /**
     * Scope for search functionality
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('nomor_antrian', 'like', "%{$term}%")
              ->orWhereHas('pengunjung', function ($pq) use ($term) {
                  $pq->where('nama_pengunjung', 'like', "%{$term}%")
                    ->orWhere('nik', 'like', "%{$term}%");
              })
              ->orWhereHas('layanan', function ($lq) use ($term) {
                  $lq->where('nama_layanan', 'like', "%{$term}%")
                    ->orWhere('kode_layanan', 'like', "%{$term}%");
              });
        });
    }
}