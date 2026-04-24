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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('peserta_id')->nullable()->constrained('pesertas')->nullOnDelete();
            $table->string('aksi');
            $table->string('keterangan')->nullable();
            $table->string('status_lama')->nullable();
            $table->string('status_baru')->nullable();
            $table->timestamps();
        });
    }
};
