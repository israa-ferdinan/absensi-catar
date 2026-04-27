<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahapUjian extends Model
{
    protected $fillable = [
        'nama',
        'is_active',
    ];

    public function absensis()
    {
        return $this->hasMany(AbsensiPeserta::class);
    }

    public function jadwals()
    {
        return $this->hasMany(JadwalUjian::class);
    }
}
