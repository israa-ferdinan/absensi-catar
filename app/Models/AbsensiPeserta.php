<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsensiPeserta extends Model
{
    protected $fillable = [
        'peserta_id',
        'tahap_ujian_id',
        'tanggal_jadwal',
        'tanggal_absen_aktual',
        'status_absen',
        'waktu_absen',
        'status_pulang',
        'waktu_pulang',
        'is_susulan',
    ];

    public function peserta()
    {
        return $this->belongsTo(Peserta::class);
    }

    public function tahapUjian()
    {
        return $this->belongsTo(TahapUjian::class);
    }
}