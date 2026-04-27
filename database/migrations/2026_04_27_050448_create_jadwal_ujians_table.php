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
        Schema::create('jadwal_ujians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahap_ujian_id')->constrained('tahap_ujians')->cascadeOnDelete();
            $table->date('tanggal');
            $table->string('kelompok')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['tahap_ujian_id', 'tanggal', 'kelompok'], 'jadwal_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_ujians');
    }
};
