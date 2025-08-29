<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $table = 'admin';
    protected $primaryKey = 'id_admin';
    protected $guarded = ['id_admin'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Relationship with Layanan
     * Admin dapat mengelola banyak layanan
     */
    public function layanan()
    {
        return $this->hasMany(Layanan::class, 'id_admin');
    }

    /**
     * Relationship with Antrian
     * Admin dapat memproses banyak antrian
     */
    public function antrian()
    {
        return $this->hasMany(Antrian::class, 'id_admin');
    }
}