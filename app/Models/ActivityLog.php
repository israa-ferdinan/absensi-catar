<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'peserta_id',
        'aksi',
        'keterangan',
        'status_lama',
        'status_baru',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function peserta()
    {
        return $this->belongsTo(Peserta::class);
    }
}