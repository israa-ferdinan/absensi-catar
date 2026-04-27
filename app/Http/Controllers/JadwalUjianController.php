<?php

namespace App\Http\Controllers;

use App\Models\JadwalUjian;
use App\Models\TahapUjian;
use Illuminate\Http\Request;

class JadwalUjianController extends Controller
{
    public function index()
    {
        $jadwals = JadwalUjian::with('tahapUjian')
            ->orderBy('tanggal')
            ->paginate(10);

        return view('jadwal_ujians.index', compact('jadwals'));
    }

    public function create()
    {
        $tahapUjians = TahapUjian::where('is_active', true)->orderBy('nama')->get();

        return view('jadwal_ujians.create', compact('tahapUjians'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tahap_ujian_id' => 'required|exists:tahap_ujians,id',
            'tanggal' => 'required|date',
            'kelompok' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        JadwalUjian::create([
            'tahap_ujian_id' => $request->tahap_ujian_id,
            'tanggal' => $request->tanggal,
            'kelompok' => $request->kelompok,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('jadwal-ujians.index')
            ->with('success', 'Jadwal ujian berhasil ditambahkan.');
    }

    public function edit(JadwalUjian $jadwalUjian)
    {
        $tahapUjians = TahapUjian::where('is_active', true)->orderBy('nama')->get();

        return view('jadwal_ujians.edit', compact('jadwalUjian', 'tahapUjians'));
    }

    public function update(Request $request, JadwalUjian $jadwalUjian)
    {
        $request->validate([
            'tahap_ujian_id' => 'required|exists:tahap_ujians,id',
            'tanggal' => 'required|date',
            'kelompok' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $jadwalUjian->update([
            'tahap_ujian_id' => $request->tahap_ujian_id,
            'tanggal' => $request->tanggal,
            'kelompok' => $request->kelompok,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('jadwal-ujians.index')
            ->with('success', 'Jadwal ujian berhasil diperbarui.');
    }

    public function destroy(JadwalUjian $jadwalUjian)
    {
        $jadwalUjian->delete();

        return redirect()->route('jadwal-ujians.index')
            ->with('success', 'Jadwal ujian berhasil dihapus.');
    }
}