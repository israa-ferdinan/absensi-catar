<?php

namespace App\Http\Controllers;

use App\Models\Peserta;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PhpOffice\PhpWord\TemplateProcessor;

class BeritaAcaraController extends Controller
{
    public function form()
    {
        return view('berita_acara.form');
    }

    public function download(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
        ]);

        $tanggal = $request->tanggal;

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

        $totalPeserta = Peserta::where('tanggal_ujian', $tanggal)->count();

        $hadir = Peserta::where('tanggal_ujian', $tanggal)
            ->where('status_absen', 'hadir')
            ->where('is_susulan', false)
            ->count();

        $tidakHadir = Peserta::where('tanggal_ujian', $tanggal)
            ->where('status_absen', 'tidak_hadir')
            ->count();

        $susulan = Peserta::where('tanggal_absen_aktual', $tanggal)
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
        $dataHadir = Peserta::where('tanggal_ujian', $tanggal)
            ->where('status_absen', 'hadir')
            ->where('is_susulan', false)
            ->orderBy('kode_pendaftar')
            ->get();

        if ($dataHadir->count() > 0) {
            $template->cloneRow('no', $dataHadir->count());

            foreach ($dataHadir as $i => $p) {
                $row = $i + 1;
                $template->setValue("no#{$row}", $row . '.');
                $template->setValue("kode_peserta#{$row}", $p->kode_pendaftar);
                $template->setValue("nama_peserta#{$row}", $p->nama);
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
                $template->setValue("kode_tidak#{$row}", $p->kode_pendaftar);
                $template->setValue("nama_tidak#{$row}", $p->nama);
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
                $template->setValue("kode_susulan#{$row}", $p->kode_pendaftar);
                $template->setValue("nama_susulan#{$row}", $p->nama);
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