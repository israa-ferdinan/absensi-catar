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
        Schema::table('pesertas', function (Blueprint $table) {
            $table->boolean('is_susulan')->default(false)->after('waktu_pulang');
            $table->date('tanggal_absen_aktual')->nullable()->after('is_susulan');
        });
    }

    public function down(): void
    {
        Schema::table('pesertas', function (Blueprint $table) {
            $table->dropColumn(['is_susulan', 'tanggal_absen_aktual']);
        });
    }
};
