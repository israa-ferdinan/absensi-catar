<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('absensi_pesertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peserta_id')->constrained('pesertas')->cascadeOnDelete();
            $table->foreignId('tahap_ujian_id')->constrained('tahap_ujians')->cascadeOnDelete();

            $table->date('tanggal_jadwal');
            $table->date('tanggal_absen_aktual')->nullable();

            $table->string('status_absen')->nullable(); // hadir / tidak_hadir
            $table->timestamp('waktu_absen')->nullable();

            $table->string('status_pulang')->nullable(); // pulang
            $table->timestamp('waktu_pulang')->nullable();

            $table->boolean('is_susulan')->default(false);

            $table->timestamps();

            $table->unique(['peserta_id', 'tahap_ujian_id', 'tanggal_jadwal'], 'absensi_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('absensi_pesertas');
    }
};
