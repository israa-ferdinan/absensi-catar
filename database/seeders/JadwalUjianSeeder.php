<?php

namespace Database\Seeders;

use App\Models\TahapUjian;
use App\Models\JadwalUjian;
use Illuminate\Database\Seeder;

class JadwalUjianSeeder extends Seeder
{
    public function run(): void
    {
        $kesehatan = TahapUjian::where('nama', 'Tes Kesehatan')->first();
        $psikotes = TahapUjian::where('nama', 'Psikotes')->first();

        if ($kesehatan) {
            foreach (['2026-04-27', '2026-04-28', '2026-04-29', '2026-04-30'] as $tanggal) {
                JadwalUjian::updateOrCreate(
                    [
                        'tahap_ujian_id' => $kesehatan->id,
                        'tanggal' => $tanggal,
                        'kelompok' => null,
                    ],
                    ['is_active' => true]
                );
            }
        }

        if ($psikotes) {
            foreach (['2026-05-01', '2026-05-02'] as $tanggal) {
                JadwalUjian::updateOrCreate(
                    [
                        'tahap_ujian_id' => $psikotes->id,
                        'tanggal' => $tanggal,
                        'kelompok' => null,
                    ],
                    ['is_active' => true]
                );
            }
        }
    }
}