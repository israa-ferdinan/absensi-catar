<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalUjian extends Model
{
    protected $fillable = [
        'tahap_ujian_id',
        'tanggal',
        'kelompok',
        'is_active',
    ];

    public function tahapUjian()
    {
        return $this->belongsTo(TahapUjian::class);
    }
}