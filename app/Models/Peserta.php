<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peserta extends Model
{
    protected $fillable = [
        'kode_pendaftar',
        'nama',
        'jenis_kelamin',
        'jurusan',
        'kelompok',
        'tanggal_ujian',
        'status_absen',
        'waktu_absen',
        'status_pulang',
        'waktu_pulang',
    ];
}
