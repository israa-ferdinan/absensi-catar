<?php

namespace App\Exports;

use App\Models\Peserta;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PesertaExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
    {
        $query = Peserta::query();

        if ($this->request->tanggal_ujian) {
            $query->where('tanggal_ujian', $this->request->tanggal_ujian);
        }

        if ($this->request->jurusan) {
            $query->where('jurusan', $this->request->jurusan);
        }

        if ($this->request->kelompok) {
            $query->where('kelompok', $this->request->kelompok);
        }

        if ($this->request->status_absen == 'belum') {
            $query->whereNull('status_absen');
        } elseif ($this->request->status_absen) {
            $query->where('status_absen', $this->request->status_absen);
        }

        return $query->orderBy('nama')->get([
            'kode_pendaftar',
            'nama',
            'jenis_kelamin',
            'jurusan',
            'kelompok',
            'tanggal_ujian',
            'status_absen',
            'waktu_absen',
        ]);
    }

    public function headings(): array
    {
        return [
            'Kode Pendaftar',
            'Nama',
            'Jenis Kelamin',
            'Jurusan',
            'Kelompok',
            'Tanggal Ujian',
            'Status Absen',
            'Waktu Absen',
        ];
    }
}