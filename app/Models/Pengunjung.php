<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Pengunjung extends Model
{
    use HasFactory;

    protected $table = 'pengunjung';
    protected $primaryKey = 'id_pengunjung';
    
    // Disable default timestamps karena menggunakan waktu_daftar
    public $timestamps = false;

    protected $fillable = [
        'nik',
        'nama_pengunjung',
        'no_hp',
        'waktu_daftar',
    ];

    protected $casts = [
        'waktu_daftar' => 'datetime',
    ];

    /**
     * Relationship with Antrian
     * Pengunjung dapat memiliki banyak antrian
     */
    public function antrian()
    {
        return $this->hasMany(Antrian::class, 'id_pengunjung', 'id_pengunjung');
    }

    /**
     * Mutator untuk NIK - hanya menyimpan angka
     */
    public function setNikAttribute($value)
    {
        $this->attributes['nik'] = preg_replace('/\D/', '', $value);
    }

    /**
     * Mutator untuk nama - kapitalisasi kata
     */
    public function setNamaPengunjungAttribute($value)
    {
        $this->attributes['nama_pengunjung'] = ucwords(strtolower(trim($value)));
    }

    /**
     * Mutator untuk no HP - format standar
     */
    public function setNoHpAttribute($value)
    {
        if ($value) {
            // Remove non-numeric characters except +
            $cleaned = preg_replace('/[^\d+]/', '', $value);
            $this->attributes['no_hp'] = $cleaned;
        }
    }

    /**
     * Accessor untuk format NIK dengan spasi
     */
    public function getFormattedNikAttribute()
    {
        $nik = $this->nik;
        if (strlen($nik) === 16) {
            return substr($nik, 0, 2) . ' ' . 
                   substr($nik, 2, 2) . ' ' . 
                   substr($nik, 4, 2) . ' ' . 
                   substr($nik, 6, 6) . ' ' . 
                   substr($nik, 12, 4);
        }
        return $nik;
    }

    /**
     * Accessor untuk waktu daftar dalam format Indonesia
     */
    public function getWaktuDaftarFormattedAttribute()
    {
        return Carbon::parse($this->waktu_daftar)->format('d/m/Y H:i:s');
    }

    /**
     * Get display name with NIK
     */
    public function getDisplayNameAttribute()
    {
        return $this->nama_pengunjung . ' (' . $this->nik . ')';
    }

    /**
     * Check if pengunjung has valid NIK (16 digits)
     */
    public function hasValidNik()
    {
        return strlen($this->nik) === 16 && is_numeric($this->nik);
    }

    /**
     * Check if pengunjung has contact info
     */
    public function hasContactInfo()
    {
        return !empty($this->no_hp);
    }

    /**
     * Get visitor statistics
     */
    public function getStatsAttribute()
    {
        $totalAntrian = $this->antrian->count();
        $selesai = $this->antrian->where('status_antrian', 'selesai')->count();
        $batal = $this->antrian->where('status_antrian', 'batal')->count();
        $aktif = $this->antrian->whereIn('status_antrian', ['menunggu', 'dipanggil'])->count();

        return [
            'total_antrian' => $totalAntrian,
            'selesai' => $selesai,
            'batal' => $batal,
            'aktif' => $aktif,
            'completion_rate' => $totalAntrian > 0 ? ($selesai / $totalAntrian) * 100 : 0,
            'first_visit' => $this->waktu_daftar,
            'last_visit' => $this->antrian()->latest('waktu_antrian')->first()?->waktu_antrian,
        ];
    }

    /**
     * Get favorite services
     */
    public function getFavoriteServicesAttribute()
{
    return $this->antrian()
        ->select('id_layanan', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
        ->with('layanan')
        ->groupBy('id_layanan')
        ->orderByDesc('total')
        ->get()
        ->map(function ($item) {
            return [
                'layanan' => $item->layanan,
                'count' => $item->total
            ];
        });
}

    /**
     * Get current active queues
     */
    public function getActiveQueuesAttribute()
    {
        return $this->antrian()
            ->with(['layanan', 'admin'])
            ->whereIn('status_antrian', ['menunggu', 'dipanggil'])
            ->orderBy('waktu_antrian', 'desc')
            ->get();
    }

    /**
     * Get visit history
     */
    public function getVisitHistoryAttribute()
    {
        return $this->antrian()
            ->with(['layanan', 'admin'])
            ->orderBy('waktu_antrian', 'desc')
            ->get();
    }

    /**
     * Check if has active queue today
     */
    public function hasActiveQueueToday()
    {
        return $this->antrian()
            ->whereDate('waktu_antrian', today())
            ->whereIn('status_antrian', ['menunggu', 'dipanggil'])
            ->exists();
    }

    /**
     * Get today's queues
     */
    public function getTodayQueuesAttribute()
    {
        return $this->antrian()
            ->with(['layanan', 'admin'])
            ->whereDate('waktu_antrian', today())
            ->orderBy('waktu_antrian', 'desc')
            ->get();
    }

    /**
     * Calculate average waiting time
     */
    public function getAverageWaitingTimeAttribute()
    {
        $completedQueues = $this->antrian()
            ->where('status_antrian', 'selesai')
            ->whereNotNull('waktu_dipanggil')
            ->get();

        if ($completedQueues->isEmpty()) {
            return 0;
        }

        $totalWaitTime = $completedQueues->sum(function ($antrian) {
            return $antrian->waktu_dipanggil->diffInMinutes($antrian->waktu_antrian);
        });

        return round($totalWaitTime / $completedQueues->count(), 2);
    }

    /**
     * Check if can be deleted
     */
    public function canBeDeleted()
    {
        // Cannot delete if has active queues
        return !$this->antrian()
            ->whereIn('status_antrian', ['menunggu', 'dipanggil'])
            ->exists();
    }

    /**
     * Scope for search functionality
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('nik', 'like', "%{$term}%")
              ->orWhere('nama_pengunjung', 'like', "%{$term}%")
              ->orWhere('no_hp', 'like', "%{$term}%");
        });
    }

    /**
     * Scope for registered today
     */
    public function scopeRegisteredToday($query)
    {
        return $query->whereDate('waktu_daftar', today());
    }

    /**
     * Scope for visitors with contact info
     */
    public function scopeWithContact($query)
    {
        return $query->whereNotNull('no_hp');
    }

    /**
     * Scope for visitors without contact info
     */
    public function scopeWithoutContact($query)
    {
        return $query->whereNull('no_hp');
    }

    /**
     * Create or update pengunjung data
     */
    public static function createOrUpdateByNik($data)
    {
        $pengunjung = self::where('nik', $data['nik'])->first();
        
        if ($pengunjung) {
            // Update existing data if needed
            if (isset($data['nama_pengunjung']) && $data['nama_pengunjung'] !== $pengunjung->nama_pengunjung) {
                $pengunjung->nama_pengunjung = $data['nama_pengunjung'];
            }
            
            if (isset($data['no_hp']) && $data['no_hp'] !== $pengunjung->no_hp) {
                $pengunjung->no_hp = $data['no_hp'];
            }
            
            $pengunjung->save();
        } else {
            // Create new pengunjung
            $data['waktu_daftar'] = now();
            $pengunjung = self::create($data);
        }
        
        return $pengunjung;
    }
}