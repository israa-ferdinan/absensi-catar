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
    public function up()
    {
        Schema::create('pesertas', function (Blueprint $table) {
        $table->id();
        $table->string('kode_pendaftar')->unique();
        $table->string('nama');
        $table->string('jenis_kelamin')->nullable();
        $table->string('jurusan')->nullable();
        $table->string('kelompok')->nullable();
        $table->date('tanggal_ujian')->nullable();
        $table->enum('status_absen', ['hadir', 'tidak_hadir'])->nullable();
        $table->timestamp('waktu_absen')->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pesertas');
    }
};
