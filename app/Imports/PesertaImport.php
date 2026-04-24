<?php

namespace App\Imports;

use App\Models\Peserta;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class PesertaImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $tanggalUjian = null;

        if (!empty($row['tanggal_ujian'])) {
            if (is_numeric($row['tanggal_ujian'])) {
                $tanggalUjian = Date::excelToDateTimeObject($row['tanggal_ujian'])->format('Y-m-d');
            } else {
                $tanggalUjian = Carbon::parse($row['tanggal_ujian'])->format('Y-m-d');
            }
        }

        return Peserta::updateOrCreate(
            [
                'kode_pendaftar' => $row['kode_pendaftar'],
            ],
            [
                'nama' => $row['nama'],
                'jenis_kelamin' => $row['jenis_kelamin'] ?? null,
                'jurusan' => $row['jurusan'] ?? null,
                'kelompok' => $row['kelompok'] ?? null,
                'tanggal_ujian' => $tanggalUjian,
            ]
        );
    }
}