<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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
        'id_admin',
        'aktif'
    ];

    // PENTING: Cast ke tipe data yang benar
    protected $casts = [
        'aktif' => 'boolean',
        'estimasi_durasi_layanan' => 'integer',
        'kapasitas_harian' => 'integer',
        'id_admin' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Default values
    protected $attributes = [
        'aktif' => true, // PENTING: Default aktif = true
    ];

    /**
     * Relationships
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    public function antrian()
    {
        return $this->hasMany(Antrian::class, 'id_layanan', 'id_layanan');
    }

    public function loket()
    {
        return $this->hasMany(Loket::class, 'id_layanan', 'id_layanan');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('aktif', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('aktif', false);
    }

    public function scopeByAdmin($query, $adminId)
    {
        return $query->where('id_admin', $adminId);
    }

    public function scopeWithTodayQueue($query)
    {
        return $query->withCount(['antrian' => function($subQuery) {
            $subQuery->whereDate('waktu_antrian', today());
        }]);
    }

    /**
     * Accessors & Mutators
     */
    public function getKodeLayananAttribute($value)
    {
        return strtoupper($value);
    }

    public function setKodeLayananAttribute($value)
    {
        $this->attributes['kode_layanan'] = strtoupper(trim($value));
    }

    public function setNamaLayananAttribute($value)
    {
        $this->attributes['nama_layanan'] = ucwords(trim($value));
    }

    /**
     * Custom Methods
     */
    public function getTodayQueueCount()
    {
        return $this->antrian()
            ->whereDate('waktu_antrian', today())
            ->count();
    }

    public function getTodayCompletedCount()
    {
        return $this->antrian()
            ->whereDate('waktu_antrian', today())
            ->where('status_antrian', 'selesai')
            ->count();
    }

    public function getTodayWaitingCount()
    {
        return $this->antrian()
            ->whereDate('waktu_antrian', today())
            ->where('status_antrian', 'menunggu')
            ->count();
    }

    public function getRemainingCapacity()
    {
        $todayCount = $this->getTodayQueueCount();
        return max(0, $this->kapasitas_harian - $todayCount);
    }

    public function getUtilizationRate()
    {
        if ($this->kapasitas_harian <= 0) {
            return 0;
        }

        $todayCount = $this->getTodayQueueCount();
        return round(($todayCount / $this->kapasitas_harian) * 100, 2);
    }

    public function getCompletionRate()
    {
        $todayTotal = $this->getTodayQueueCount();
        
        if ($todayTotal <= 0) {
            return 0;
        }

        $todayCompleted = $this->getTodayCompletedCount();
        return round(($todayCompleted / $todayTotal) * 100, 2);
    }

    public function isAvailable()
    {
        return $this->aktif && $this->getRemainingCapacity() > 0;
    }

    public function getNextQueueNumber()
    {
        $count = $this->getTodayQueueCount();
        $nextNumber = $count + 1;
        
        return $this->kode_layanan . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Boot method untuk event handling
     */
    protected static function boot()
    {
        parent::boot();

        // Event saat layanan dibuat
        static::created(function ($layanan) {
            \Log::info('Layanan created:', [
                'id' => $layanan->id_layanan,
                'nama' => $layanan->nama_layanan,
                'kode' => $layanan->kode_layanan,
                'aktif' => $layanan->aktif
            ]);
        });

        // Event saat layanan diupdate
        static::updated(function ($layanan) {
            \Log::info('Layanan updated:', [
                'id' => $layanan->id_layanan,
                'nama' => $layanan->nama_layanan,
                'aktif' => $layanan->aktif,
                'changes' => $layanan->getChanges()
            ]);
        });

        // Event saat layanan dihapus
        static::deleted(function ($layanan) {
            \Log::info('Layanan deleted:', [
                'id' => $layanan->id_layanan,
                'nama' => $layanan->nama_layanan
            ]);
        });
    }
}