<?php

namespace App\Http\Controllers;

use App\Models\Peserta;
use App\Models\TahapUjian;
use App\Models\JadwalUjian;
use App\Models\AbsensiPeserta;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PhpOffice\PhpWord\TemplateProcessor;

class BeritaAcaraController extends Controller
{
    public function form(Request $request)
    {
        $tahapUjians = TahapUjian::where('is_active', true)
            ->orderBy('nama')
            ->get();

        $tahapUjianId = $request->tahap_ujian_id ?? optional($tahapUjians->first())->id;

        $jadwals = JadwalUjian::where('tahap_ujian_id', $tahapUjianId)
            ->where('is_active', true)
            ->orderBy('tanggal')
            ->get();

        $jadwalUjianId = $request->jadwal_ujian_id;

        // PENTING: kalau jadwal yang dikirim bukan milik tahap yang dipilih,
        // paksa ambil jadwal pertama dari tahap tersebut
        if (!$jadwalUjianId || !$jadwals->contains('id', (int) $jadwalUjianId)) {
            $jadwalUjianId = optional($jadwals->first())->id;
        }

        return view('berita_acara.form', compact(
            'tahapUjians',
            'tahapUjianId',
            'jadwals',
            'jadwalUjianId'
        ));
    }

    public function download(Request $request)
    {
        $request->validate([
            'tahap_ujian_id' => 'required|exists:tahap_ujians,id',
            'jadwal_ujian_id' => 'required|exists:jadwal_ujians,id',
        ]);

        $tahap = \App\Models\TahapUjian::findOrFail($request->tahap_ujian_id);
        $jadwal = \App\Models\JadwalUjian::findOrFail($request->jadwal_ujian_id);

        $tanggal = $jadwal->tanggal;

        Carbon::setLocale('id');

        $tanggalObj = Carbon::parse($tanggal);

        $hari = $tanggalObj->translatedFormat('l');
        $bulan = $tanggalObj->translatedFormat('F');

        $tanggalTerbilang = trim(preg_replace('/\s+/', ' ', terbilang((int) $tanggalObj->format('d'))));
        $tahunTerbilang = trim(preg_replace('/\s+/', ' ', terbilang((int) $tanggalObj->format('Y'))));

        $tanggalFormatted = $tanggalObj->translatedFormat('d F Y');
        $tanggalLampiran = strtoupper($tanggalObj->translatedFormat('l, d F Y'));

        $template = new TemplateProcessor(
            storage_path('app/templates/berita_acara.docx')
        );

        $template->setValue('hari', $hari);
        $template->setValue('tanggal_terbilang', $tanggalTerbilang);
        $template->setValue('bulan', $bulan);
        $template->setValue('tahun_terbilang', $tahunTerbilang);
        $template->setValue('tanggal_cetak', $tanggalFormatted);
        $template->setValue('tanggal_lampiran_hadir', $tanggalLampiran);
        $template->setValue('tanggal_lampiran_tidak_hadir', $tanggalLampiran);
        $template->setValue('tanggal_lampiran_susulan', $tanggalLampiran);
        $template->setValue('nama_tahap', $tahap->nama);
        $template->setValue('nama_tahap_upper', strtoupper($tahap->nama));

        $pesertaQuery = Peserta::query();

        if ($jadwal->kelompok) {
            $pesertaQuery->where('kelompok', $jadwal->kelompok);
        }

        $pesertaIds = $pesertaQuery->pluck('id');

        $totalPeserta = $pesertaIds->count();

        $absensiQuery = AbsensiPeserta::whereIn('peserta_id', $pesertaIds)
            ->where('tahap_ujian_id', $tahap->id)
            ->where('tanggal_jadwal', $tanggal);

        $hadir = (clone $absensiQuery)
            ->where('status_absen', 'hadir')
            ->where('is_susulan', false)
            ->count();

        $tidakHadir = (clone $absensiQuery)
            ->where('status_absen', 'tidak_hadir')
            ->count();

        $susulan = (clone $absensiQuery)
            ->where('status_absen', 'hadir')
            ->where('is_susulan', true)
            ->count();

        $totalHadir = $hadir + $susulan;

        $template->setValue('total_peserta', $totalPeserta);
        $template->setValue('hadir', $hadir);
        $template->setValue('tidak_hadir', $tidakHadir);
        $template->setValue('susulan', $susulan);
        $template->setValue('total_hadir', $totalHadir);

        $template->setValue('laporan_penyelenggara', $request->laporan_penyelenggara ?: 'NIHIL');
        $template->setValue('laporan_panitia', $request->laporan_panitia ?: 'NIHIL');

        // Lampiran Hadir
        $dataHadir = AbsensiPeserta::with('peserta')
            ->whereIn('peserta_id', $pesertaIds)
            ->where('tahap_ujian_id', $tahap->id)
            ->where('tanggal_jadwal', $tanggal)
            ->where('status_absen', 'hadir')
            ->where('is_susulan', false)
            ->get()
            ->sortBy(fn($a) => $a->peserta->kode_pendaftar);

        if ($dataHadir->count() > 0) {
            $template->cloneRow('no', $dataHadir->count());

            foreach ($dataHadir as $i => $p) {
                $row = $i + 1;
                $template->setValue("no#{$row}", $row . '.');
                $template->setValue("kode_peserta#{$row}", $p->peserta->kode_pendaftar);
                $template->setValue("nama_peserta#{$row}", $p->peserta->nama);
                $template->setValue("keterangan#{$row}", 'HADIR');
            }
        } else {
            $template->setValue('no', '-');
            $template->setValue('kode_peserta', '-');
            $template->setValue('nama_peserta', '-');
            $template->setValue('keterangan', '-');
        }

        // Lampiran Tidak Hadir
        $dataTidakHadir = Peserta::where('tanggal_ujian', $tanggal)
            ->where('status_absen', 'tidak_hadir')
            ->orderBy('kode_pendaftar')
            ->get();

        if ($dataTidakHadir->count() > 0) {
            $template->cloneRow('no_tidak', $dataTidakHadir->count());

            foreach ($dataTidakHadir as $i => $p) {
                $row = $i + 1;
                $template->setValue("no_tidak#{$row}", $row);
                $template->setValue("kode_tidak#{$row}", $p->peserta->kode_pendaftar);
                $template->setValue("nama_tidak#{$row}", $p->peserta->nama);
                $template->setValue("ket_tidak#{$row}", 'TIDAK HADIR');
            }
        } else {
            $template->setValue('no_tidak', '-');
            $template->setValue('kode_tidak', '-');
            $template->setValue('nama_tidak', '-');
            $template->setValue('ket_tidak', '-');
        }

        // Lampiran Susulan
        $dataSusulan = Peserta::where('tanggal_absen_aktual', $tanggal)
            ->where('status_absen', 'hadir')
            ->where('is_susulan', true)
            ->orderBy('kode_pendaftar')
            ->get();

        if ($dataSusulan->count() > 0) {
            $template->cloneRow('no_susulan', $dataSusulan->count());

            foreach ($dataSusulan as $i => $p) {
                $row = $i + 1;
                $template->setValue("no_susulan#{$row}", $row . '.');
                $template->setValue("kode_susulan#{$row}", $p->peserta->kode_pendaftar);
                $template->setValue("nama_susulan#{$row}", $p->peserta->nama);
                $template->setValue("ket_susulan#{$row}", 'Hadir Susulan');
            }
        } else {
            $template->setValue('no_susulan', '-');
            $template->setValue('kode_susulan', '-');
            $template->setValue('nama_susulan', '-');
            $template->setValue('ket_susulan', '-');
        }

        $filename = 'berita_acara_' . $tanggal . '.docx';
        $path = storage_path('app/public/' . $filename);

        $template->saveAs($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }
}