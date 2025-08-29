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
        'id_admin',
        'waktu_antrian',
        'waktu_estimasi',
        'waktu_dipanggil',
        'status_antrian'
    ];

    // PERBAIKAN: Hapus $dates dan gunakan hanya $casts
    // MASALAH SEBELUMNYA: Ada konflik antara $dates dan $casts
    protected $casts = [
        'waktu_antrian' => 'datetime',
        'waktu_estimasi' => 'datetime',
        'waktu_dipanggil' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'id_pengunjung' => 'integer',
        'id_layanan' => 'integer',
        'id_admin' => 'integer'
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
     */
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'id_admin', 'id_admin');
    }

    /**
     * Relationship with Pengunjung
     */
    public function pengunjung()
    {
        return $this->belongsTo(Pengunjung::class, 'id_pengunjung', 'id_pengunjung');
    }

    /**
     * Relationship with Layanan
     */
    public function layanan()
    {
        return $this->belongsTo(Layanan::class, 'id_layanan', 'id_layanan');
    }

    /**
     * Scopes
     */
    public function scopeMenunggu($query)
    {
        return $query->where('status_antrian', self::STATUS_MENUNGGU);
    }

    public function scopeDipanggil($query)
    {
        return $query->where('status_antrian', self::STATUS_DIPANGGIL);
    }

    public function scopeSelesai($query)
    {
        return $query->where('status_antrian', self::STATUS_SELESAI);
    }

    public function scopeBatal($query)
    {
        return $query->where('status_antrian', self::STATUS_BATAL);
    }

    public function scopeAktif($query)
    {
        return $query->whereIn('status_antrian', [self::STATUS_MENUNGGU, self::STATUS_DIPANGGIL]);
    }

    public function scopeHariIni($query)
    {
        return $query->whereDate('waktu_antrian', Carbon::today());
    }

    public function scopeOnDate($query, $date)
    {
        return $query->whereDate('waktu_antrian', $date);
    }

    public function scopeByLayanan($query, $layananId)
    {
        return $query->where('id_layanan', $layananId);
    }

    /**
     * PERBAIKAN: Accessor dengan null check
     */
    public function getWaktuAntrianFormattedAttribute()
    {
        return $this->waktu_antrian ? $this->waktu_antrian->format('d/m/Y H:i:s') : null;
    }

    public function getWaktuEstimasiFormattedAttribute()
    {
        return $this->waktu_estimasi ? $this->waktu_estimasi->format('H:i') : null;
    }

    public function getWaktuDipanggilFormattedAttribute()
    {
        return $this->waktu_dipanggil ? $this->waktu_dipanggil->format('d/m/Y H:i:s') : null;
    }

    /**
     * Check status methods
     */
    public function isActive()
    {
        return in_array($this->status_antrian, [self::STATUS_MENUNGGU, self::STATUS_DIPANGGIL]);
    }

    public function isCompleted()
    {
        return in_array($this->status_antrian, [self::STATUS_SELESAI, self::STATUS_BATAL]);
    }

    /**
     * Status badge class for UI
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
     * Status methods
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

    public function completeQueue($adminId = null)
    {
        $this->status_antrian = self::STATUS_SELESAI;
        if ($adminId) {
            $this->id_admin = $adminId;
        }
        
        return $this->save();
    }

    public function cancelQueue($adminId = null)
    {
        $this->status_antrian = self::STATUS_BATAL;
        if ($adminId) {
            $this->id_admin = $adminId;
        }
        
        return $this->save();
    }

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
     * Check if queue can be updated to specific status
     */
    public function canUpdateToStatus($newStatus)
    {
        $allowedTransitions = [
            self::STATUS_MENUNGGU => [self::STATUS_DIPANGGIL, self::STATUS_BATAL],
            self::STATUS_DIPANGGIL => [self::STATUS_SELESAI, self::STATUS_BATAL, self::STATUS_MENUNGGU],
            self::STATUS_SELESAI => [],
            self::STATUS_BATAL => [self::STATUS_MENUNGGU],
        ];

        return in_array($newStatus, $allowedTransitions[$this->status_antrian] ?? []);
    }
}