<?php

namespace Database\Seeders;

use App\Models\TahapUjian;
use Illuminate\Database\Seeder;

class TahapUjianSeeder extends Seeder
{
    public function run(): void
    {
        TahapUjian::updateOrCreate(
            ['nama' => 'Tes Kesehatan'],
            ['is_active' => true]
        );

        TahapUjian::updateOrCreate(
            ['nama' => 'Psikotes'],
            ['is_active' => true]
        );
    }
    
}