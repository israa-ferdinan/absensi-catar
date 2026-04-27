<?php

namespace App\Http\Controllers;

use App\Models\TahapUjian;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TahapUjianController extends Controller
{
    public function index()
    {
        $tahapUjians = TahapUjian::orderBy('nama')->paginate(10);

        return view('tahap_ujians.index', compact('tahapUjians'));
    }

    public function create()
    {
        return view('tahap_ujians.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:255', 'unique:tahap_ujians,nama'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        TahapUjian::create([
            'nama' => $request->nama,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('tahap-ujians.index')
            ->with('success', 'Tahap ujian berhasil ditambahkan.');
    }

    public function edit(TahapUjian $tahapUjian)
    {
        return view('tahap_ujians.edit', compact('tahapUjian'));
    }

    public function update(Request $request, TahapUjian $tahapUjian)
    {
        $request->validate([
            'nama' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tahap_ujians', 'nama')->ignore($tahapUjian->id),
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $tahapUjian->update([
            'nama' => $request->nama,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('tahap-ujians.index')
            ->with('success', 'Tahap ujian berhasil diperbarui.');
    }

    public function destroy(TahapUjian $tahapUjian)
    {
        $tahapUjian->delete();

        return redirect()->route('tahap-ujians.index')
            ->with('success', 'Tahap ujian berhasil dihapus.');
    }
}