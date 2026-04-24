<?php

namespace App\Http\Controllers;

use App\Models\Peserta;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->tanggal_ujian 
            ?? Peserta::whereNotNull('tanggal_ujian')
                ->orderBy('tanggal_ujian')
                ->value('tanggal_ujian');

        $query = Peserta::query()
            ->where('tanggal_ujian', $tanggal);

        $totalPeserta = (clone $query)->count();
        $totalHadir = (clone $query)->where('status_absen', 'hadir')->count();
        $totalTidakHadir = (clone $query)->where('status_absen', 'tidak_hadir')->count();
        $totalBelumAbsen = (clone $query)->whereNull('status_absen')->count();
        $totalSudahPulang = (clone $query)->where('status_pulang', 'pulang')->count();
        $totalBelumPulang = (clone $query)
            ->where('status_absen', 'hadir')
            ->whereNull('status_pulang')
            ->count();

        $persenHadir = $totalPeserta > 0
            ? round(($totalHadir / $totalPeserta) * 100)
            : 0;

        $tanggalUjianList = Peserta::select('tanggal_ujian')
            ->distinct()
            ->whereNotNull('tanggal_ujian')
            ->orderBy('tanggal_ujian')
            ->pluck('tanggal_ujian');

        $recentLogs = ActivityLog::with(['user', 'peserta'])
            ->latest()
            ->limit(10)
            ->get();

        $jurusanStats = Peserta::selectRaw("
            jurusan,
            COUNT(*) as total,
            SUM(CASE WHEN status_absen = 'hadir' THEN 1 ELSE 0 END) as hadir,
            SUM(CASE WHEN status_absen = 'tidak_hadir' THEN 1 ELSE 0 END) as tidak_hadir,
            SUM(CASE WHEN status_absen IS NULL THEN 1 ELSE 0 END) as belum_absen
        ")
        ->where('tanggal_ujian', $tanggal)
        ->groupBy('jurusan')
        ->orderBy('jurusan')
        ->get();    

        return view('dashboard.index', compact(
            'tanggal',
            'tanggalUjianList',
            'totalPeserta',
            'totalHadir',
            'totalTidakHadir',
            'totalBelumAbsen',
            'totalSudahPulang',
            'totalBelumPulang',
            'persenHadir',
            'recentLogs',
            'jurusanStats',
        ));
    }
}