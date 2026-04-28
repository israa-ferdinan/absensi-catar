<?php

namespace App\Http\Controllers;

use App\Models\Peserta;
use App\Models\ActivityLog;
use App\Imports\PesertaImport;
use App\Exports\PesertaExport;
use App\Models\TahapUjian;
use App\Models\AbsensiPeserta;
use Illuminate\Http\Request;
use App\Models\JadwalUjian;
use Maatwebsite\Excel\Facades\Excel;

class PesertaController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $tahapUjians = TahapUjian::where('is_active', true)
            ->orderBy('nama')
            ->get();

        $tahapUjianId = $request->tahap_ujian_id ?? optional($tahapUjians->first())->id;

        $jadwals = JadwalUjian::where('tahap_ujian_id', $tahapUjianId)
            ->where('is_active', true)
            ->orderBy('tanggal')
            ->get();

        $jadwalUjianId = $request->jadwal_ujian_id ?? optional($jadwals->first())->id;
        $jadwalAktif = JadwalUjian::find($jadwalUjianId);

        $tanggal = $jadwalAktif->tanggal ?? now()->format('Y-m-d');

        $pesertas = Peserta::query()
            ->when($jadwalAktif && $jadwalAktif->kelompok, function ($query) use ($jadwalAktif) {
                $query->where('kelompok', $jadwalAktif->kelompok);
            })
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode_pendaftar', 'like', "%{$search}%");
                });
            })
            ->with(['absensis' => function ($query) use ($tanggal, $tahapUjianId) {
                $query->where('tanggal_jadwal', $tanggal)
                    ->where('tahap_ujian_id', $tahapUjianId);
            }])
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        return view('peserta.index', compact(
            'pesertas',
            'search',
            'tanggal',
            'tahapUjianId',
            'tahapUjians',
            'jadwals',
            'jadwalUjianId',
            'jadwalAktif'
        ));
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
            'tahap_ujian_id' => 'required|exists:tahap_ujians,id',
            'tanggal_absen_aktual' => 'required|date',
        ]);

        $tanggalJadwal = $request->tanggal_absen_aktual;
        $tanggalAktual = $request->tanggal_absen_aktual;
        $tanggalServer = now()->timezone('Asia/Jakarta')->format('Y-m-d');

        $isSusulan = $request->status === 'hadir' && $tanggalAktual !== $tanggalServer;

        $absensi = AbsensiPeserta::firstOrNew([
            'peserta_id' => $peserta->id,
            'tahap_ujian_id' => $request->tahap_ujian_id,
            'tanggal_jadwal' => $tanggalJadwal,
        ]);

        $statusLama = $absensi->status_absen;

        $absensi->fill([
            'tanggal_absen_aktual' => $tanggalAktual,
            'status_absen' => $request->status,
            'waktu_absen' => now(),
            'is_susulan' => $isSusulan,
        ]);

        if ($request->status === 'tidak_hadir') {
            $absensi->status_pulang = null;
            $absensi->waktu_pulang = null;
        }

        $absensi->save();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'peserta_id' => $peserta->id,
            'aksi' => 'absen_masuk',
            'keterangan' => 'Update status absen masuk',
            'status_lama' => $statusLama,
            'status_baru' => $absensi->status_absen,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status absensi berhasil disimpan',
            'status' => $absensi->status_absen,
        ]);
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
        $request->validate([
            'tahap_ujian_id' => 'required|exists:tahap_ujians,id',
            'tanggal_jadwal' => 'required|date',
        ]);

        $peserta = Peserta::findOrFail($id);

        $absensi = AbsensiPeserta::where('peserta_id', $peserta->id)
            ->where('tahap_ujian_id', $request->tahap_ujian_id)
            ->where('tanggal_jadwal', $request->tanggal_jadwal)
            ->first();

        if (!$absensi) {
            return response()->json([
                'success' => true,
                'message' => 'Data absensi sudah kosong.'
            ]);
        }

        $statusLama = $absensi->status_absen . ' / ' . $absensi->status_pulang;

        $absensi->update([
            'status_absen' => null,
            'waktu_absen' => null,
            'status_pulang' => null,
            'waktu_pulang' => null,
            'is_susulan' => false,
            'tanggal_absen_aktual' => null,
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'peserta_id' => $peserta->id,
            'aksi' => 'reset_absensi',
            'keterangan' => 'Reset absensi per tahap ujian',
            'status_lama' => $statusLama,
            'status_baru' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absensi berhasil direset'
        ]);
    }

    public function kiosk()
    {
        $tahapUjians = TahapUjian::where('is_active', true)
            ->orderBy('nama')
            ->get();

        return view('peserta.kiosk', compact('tahapUjians'));
    }

    public function kioskSearch(Request $request)
    {
        $search = $request->search;
        $tanggalUjian = $request->tanggal_ujian;
        $tahapUjianId = $request->tahap_ujian_id;
        $jadwalUjianId = $request->jadwal_ujian_id;

        $jadwal = \App\Models\JadwalUjian::find($jadwalUjianId);

        $pesertas = Peserta::query()
            ->when($jadwal && $jadwal->kelompok, function ($query) use ($jadwal) {
                $query->where('kelompok', $jadwal->kelompok);
            })
            ->where(function ($query) use ($search) {
                $query->where('kode_pendaftar', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%");
            })
            ->with(['absensis' => function ($query) use ($tanggalUjian, $tahapUjianId) {
                $query->where('tanggal_jadwal', $tanggalUjian)
                    ->where('tahap_ujian_id', $tahapUjianId);
            }])
            ->orderByRaw("CASE WHEN kode_pendaftar = ? THEN 0 ELSE 1 END", [$search])
            ->orderBy('nama')
            ->limit(10)
            ->get()
            ->map(function ($peserta) {
                $absensi = $peserta->absensis->first();

                return [
                    'id' => $peserta->id,
                    'kode_pendaftar' => $peserta->kode_pendaftar,
                    'nama' => $peserta->nama,
                    'jenis_kelamin' => $peserta->jenis_kelamin,
                    'jurusan' => $peserta->jurusan,
                    'kelompok' => $peserta->kelompok,
                    'tanggal_ujian' => $peserta->tanggal_ujian,

                    'status_absen' => $absensi->status_absen ?? null,
                    'waktu_absen' => $absensi->waktu_absen ?? null,
                    'status_pulang' => $absensi->status_pulang ?? null,
                    'waktu_pulang' => $absensi->waktu_pulang ?? null,
                    'is_susulan' => $absensi->is_susulan ?? false,
                    'tanggal_absen_aktual' => $absensi->tanggal_absen_aktual ?? null,
                ];
            });

        return response()->json([
            'found' => $pesertas->isNotEmpty(),
            'pesertas' => $pesertas,
        ]);
    } 
    
    public function kioskSummary(Request $request)
    {
        $tanggalUjian = $request->tanggal_ujian;
        $tahapUjianId = $request->tahap_ujian_id;
        $jadwal = \App\Models\JadwalUjian::find($request->jadwal_ujian_id);

        $pesertaQuery = Peserta::query();

        if ($jadwal && $jadwal->kelompok) {
            $pesertaQuery->where('kelompok', $jadwal->kelompok);
        }

        $pesertaIds = $pesertaQuery->pluck('id');

        $query = AbsensiPeserta::query()
            ->whereIn('peserta_id', $pesertaIds)
            ->where('tanggal_jadwal', $tanggalUjian)
            ->where('tahap_ujian_id', $tahapUjianId);

        $hadir = (clone $query)->where('status_absen', 'hadir')->count();
        $tidakHadir = (clone $query)->where('status_absen', 'tidak_hadir')->count();
        $totalPeserta = $pesertaIds->count();

        return response()->json([
            'hadir' => $hadir,
            'tidak_hadir' => $tidakHadir,
            'belum_absen' => $totalPeserta - $hadir - $tidakHadir,
            'total' => $totalPeserta,
        ]);
    }

    public function pulang(Request $request, $id)
    {
        $request->validate([
            'tahap_ujian_id' => 'required|exists:tahap_ujians,id',
            'tanggal_jadwal' => 'required|date',
        ]);

        $peserta = Peserta::findOrFail($id);

        $absensi = AbsensiPeserta::where('peserta_id', $peserta->id)
            ->where('tahap_ujian_id', $request->tahap_ujian_id)
            ->where('tanggal_jadwal', $request->tanggal_jadwal)
            ->first();

        if (!$absensi || $absensi->status_absen !== 'hadir') {
            return response()->json([
                'success' => false,
                'message' => 'Peserta belum absen hadir.'
            ], 422);
        }

        if ($absensi->status_pulang === 'pulang') {
            return response()->json([
                'success' => false,
                'message' => 'Peserta sudah absen pulang.'
            ], 422);
        }

        $statusLama = $absensi->status_pulang;

        $absensi->update([
            'status_pulang' => 'pulang',
            'waktu_pulang' => now(),
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'peserta_id' => $peserta->id,
            'aksi' => 'absen_pulang',
            'keterangan' => 'Update status absen pulang',
            'status_lama' => $statusLama,
            'status_baru' => $absensi->status_pulang,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen pulang berhasil disimpan',
            'status_pulang' => $absensi->status_pulang,
        ]);
    }
}
