<?php

namespace App\Http\Controllers;

use App\Models\Peserta;
use App\Models\ActivityLog;
use App\Imports\PesertaImport;
use App\Exports\PesertaExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class PesertaController extends Controller
{
    public function index (Request $request)
    {
        $search = $request->search;

        $pesertas = Peserta::query()
            ->when($search, function ($query) use ($search) {
                $query->where('nama', 'like', "%{$search}%")
                ->orWhere('kode_pendaftar', 'like', "%{$search}%");
            })
            ->orderBy('nama')
            ->paginate(10);

            return view('peserta.index', compact('pesertas', 'search'));
    }

    public function import(Request $request)
    {
    $request->validate([
        'file' => 'required|mimes:xlsx,xls,csv',
    ]);

    Excel::import(new PesertaImport, $request->file('file'));

    return redirect()->route('peserta.index')
        ->with('success', 'Data peserta berhasil diupload.');
    }

    public function absen(Request $request, $id)
    {
    $peserta = Peserta::findOrFail($id);

    $request->validate([
        'status' => 'required|in:hadir,tidak_hadir',
    ]);

    $statusLama = $peserta->status_absen;

    $peserta->update([
        'status_absen' => $request->status,
        'waktu_absen' => now(),
    ]);

    ActivityLog::create([
        'user_id' => auth()->id(),
        'peserta_id' => $peserta->id,
        'aksi' => 'absen_masuk',
        'keterangan' => 'Update status absen masuk',
        'status_lama' => $statusLama,
        'status_baru' => $peserta->status_absen,
    ]);

    if ($request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => 'Status absensi berhasil disimpan',
            'status' => $peserta->status_absen,
        ]);
    }

    return back()->with('success', 'Status absensi berhasil disimpan');
    }

    public function laporan(Request $request)
    {
    $baseQuery = Peserta::query();

    if ($request->tanggal_ujian) {
        $baseQuery->where('tanggal_ujian', $request->tanggal_ujian);
    }

    if ($request->jurusan) {
        $baseQuery->where('jurusan', $request->jurusan);
    }

    if ($request->kelompok) {
        $baseQuery->where('kelompok', $request->kelompok);
    }

    // Hitung summary card TANPA filter status_absen
    $totalHadir = (clone $baseQuery)->where('status_absen', 'hadir')->count();
    $totalTidakHadir = (clone $baseQuery)->where('status_absen', 'tidak_hadir')->count();
    $totalBelumAbsen = (clone $baseQuery)->whereNull('status_absen')->count();

    // Query tabel: baru ditambah filter status_absen
    $tableQuery = clone $baseQuery;

    if ($request->status_absen == 'belum') {
        $tableQuery->whereNull('status_absen');
    } elseif ($request->status_absen) {
        $tableQuery->where('status_absen', $request->status_absen);
    }

    #$pesertas = $tableQuery->orderBy('nama')->paginate(10)->withQueryString();
    $pesertas = $tableQuery
    ->orderBy('nama')
    ->paginate(10)
    ->withQueryString();

    $jurusans = Peserta::select('jurusan')->distinct()->whereNotNull('jurusan')->pluck('jurusan');
    $kelompoks = Peserta::select('kelompok')->distinct()->whereNotNull('kelompok')->pluck('kelompok');

    return view('peserta.laporan', compact(
        'pesertas',
        'totalHadir',
        'totalTidakHadir',
        'totalBelumAbsen',
        'jurusans',
        'kelompoks'
    ));
    }

    public function export(Request $request)
    {
    return Excel::download(new PesertaExport($request), 'laporan-absensi-catar.xlsx');
    }

    public function reset(Request $request, $id)
    {
    $peserta = Peserta::findOrFail($id);

    $statusLama = $peserta->status_absen . ' / ' . $peserta->status_pulang;

    $peserta->update([
        'status_absen' => null,
        'waktu_absen' => null,
        'status_pulang' => null,
        'waktu_pulang' => null,
    ]);

    ActivityLog::create([
        'user_id' => auth()->id(),
        'peserta_id' => $peserta->id,
        'aksi' => 'reset_absensi',
        'keterangan' => 'Reset absen masuk dan pulang',
        'status_lama' => $statusLama,
        'status_baru' => null,
    ]);

    if ($request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil direset'
        ]);
    }

    return back()->with('success', 'Absensi berhasil direset');
    }

    public function kiosk()
    {
    return view('peserta.kiosk');
    }

    public function kioskSearch(Request $request)
    {
        $search = $request->search;
        $tanggalUjian = $request->tanggal_ujian;

        $pesertas = Peserta::query()
            ->when($tanggalUjian, function ($query) use ($tanggalUjian) {
                $query->where('tanggal_ujian', $tanggalUjian);
            })
            ->where(function ($query) use ($search) {
                $query->where('kode_pendaftar', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%");
            })
            ->orderByRaw("CASE WHEN kode_pendaftar = ? THEN 0 ELSE 1 END", [$search])
            ->orderBy('nama')
            ->limit(10)
            ->get();

        if ($pesertas->isEmpty()) {
            return response()->json([
                'found' => false
            ]);
        }

        return response()->json([
            'found' => true,
            'pesertas' => $pesertas
        ]);
    } 
    
    public function kioskSummary(Request $request)
    {
    $tanggalUjian = $request->tanggal_ujian;

    $query = Peserta::query();

    if ($tanggalUjian) {
        $query->where('tanggal_ujian', $tanggalUjian);
    }

    return response()->json([
        'hadir' => (clone $query)->where('status_absen', 'hadir')->count(),
        'tidak_hadir' => (clone $query)->where('status_absen', 'tidak_hadir')->count(),
        'belum_absen' => (clone $query)->whereNull('status_absen')->count(),
        'total' => (clone $query)->count(),
    ]);
    }

    public function pulang(Request $request, $id)
    {
        $peserta = Peserta::findOrFail($id);

        if ($peserta->status_absen !== 'hadir') {
            return response()->json([
                'success' => false,
                'message' => 'Peserta belum absen hadir.'
            ], 422);
        }

        if ($peserta->status_pulang === 'pulang') {
            return response()->json([
                'success' => false,
                'message' => 'Peserta sudah absen pulang.'
            ], 422);
        }

        $statusLama = $peserta->status_pulang;

        $peserta->update([
            'status_pulang' => 'pulang',
            'waktu_pulang' => now(),
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'peserta_id' => $peserta->id,
            'aksi' => 'absen_pulang',
            'keterangan' => 'Update status absen pulang',
            'status_lama' => $statusLama,
            'status_baru' => $peserta->status_pulang,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen pulang berhasil disimpan',
            'status_pulang' => $peserta->status_pulang,
        ]);
    }
}
