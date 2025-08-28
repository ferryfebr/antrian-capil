<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admin';
    protected $primaryKey = 'id_admin';

    protected $fillable = [
        'username',
        'password',
        'nama_admin',
        'email',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'email_verified_at' => 'datetime',
    ];

    /**
     * Relationship with Layanan
     * Admin dapat mengelola banyak layanan
     */
    public function layanan()
    {
        return $this->hasMany(Layanan::class, 'id_admin', 'id_admin');
    }

    /**
     * Relationship with Antrian
     * Admin dapat memproses banyak antrian
     */
    public function antrian()
    {
        return $this->hasMany(Antrian::class, 'id_admin', 'id_admin');
    }

    /**
     * Get admin's full name for display
     */
    public function getDisplayNameAttribute()
    {
        return $this->nama_admin . ' (' . $this->username . ')';
    }

    /**
     * Check if admin has any active responsibilities
     */
    public function hasActiveResponsibilities()
    {
        return $this->layanan()->where('aktif', true)->exists() || 
               $this->antrian()->whereIn('status_antrian', ['menunggu', 'dipanggil'])->exists();
    }

    /**
     * Get admin statistics
     */
    public function getStatsAttribute()
    {
        return [
            'total_layanan' => $this->layanan()->count(),
            'layanan_aktif' => $this->layanan()->where('aktif', true)->count(),
            'total_antrian' => $this->antrian()->count(),
            'antrian_hari_ini' => $this->antrian()->whereDate('created_at', today())->count(),
        ];
    }
}