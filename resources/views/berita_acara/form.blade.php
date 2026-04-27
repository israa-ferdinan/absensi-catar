@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="page-title mb-1">Berita Acara</h3>
        <div class="text-muted">
            Generate dokumen Berita Acara berdasarkan tahap dan jadwal ujian.
        </div>
    </div>
</div>

<div class="card page-card">
    <div class="card-body">

        {{-- ========================= --}}
        {{-- FORM FILTER (GET) --}}
        {{-- ========================= --}}
        <form method="GET" action="{{ route('berita-acara.form') }}" class="mb-4">

            {{-- Tahap --}}
            <div class="mb-3">
                <label class="form-label fw-bold">Tahap Ujian</label>
                <select name="tahap_ujian_id"
                    class="form-select"
                    onchange="window.location.href='{{ route('berita-acara.form') }}?tahap_ujian_id=' + this.value">

                    @foreach($tahapUjians as $tahap)
                        <option value="{{ $tahap->id }}"
                            {{ $tahapUjianId == $tahap->id ? 'selected' : '' }}>
                            {{ $tahap->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Jadwal --}}
            <div class="mb-3">
                <label class="form-label fw-bold">Jadwal Ujian</label>
                <select name="jadwal_ujian_id"
                        class="form-select"
                        onchange="this.form.submit()">

                    @foreach($jadwals as $jadwal)
                        <option value="{{ $jadwal->id }}"
                            {{ $jadwalUjianId == $jadwal->id ? 'selected' : '' }}>
                            {{ $jadwal->tanggal }}
                            {{ $jadwal->kelompok ? ' - Kelompok '.$jadwal->kelompok : ' - Semua Kelompok' }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>

        {{-- ========================= --}}
        {{-- FORM DOWNLOAD (POST) --}}
        {{-- ========================= --}}
        <form method="POST" action="{{ route('berita-acara.download') }}">
            @csrf

            {{-- Hidden (PENTING) --}}
            <input type="hidden" name="tahap_ujian_id" value="{{ $tahapUjianId }}">
            <input type="hidden" name="jadwal_ujian_id" value="{{ $jadwalUjianId }}">

            {{-- Laporan Penyelenggara --}}
            <div class="mb-3">
                <label class="form-label fw-bold">
                    Laporan Kejadian Penyelenggara Tes
                </label>
                <textarea name="laporan_penyelenggara"
                          class="form-control"
                          rows="4"
                          placeholder="Kosongkan jika tidak ada. Akan otomatis menjadi NIHIL.">{{ old('laporan_penyelenggara') }}</textarea>
            </div>

            {{-- Laporan Panitia --}}
            <div class="mb-3">
                <label class="form-label fw-bold">
                    Laporan Kejadian Panitia Penyelenggara
                </label>
                <textarea name="laporan_panitia"
                          class="form-control"
                          rows="4"
                          placeholder="Kosongkan jika tidak ada. Akan otomatis menjadi NIHIL.">{{ old('laporan_panitia') }}</textarea>
            </div>

            {{-- ACTION --}}
            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('dashboard.index') }}"
                   class="btn btn-outline-secondary">
                    Kembali
                </a>

                <button type="submit" class="btn btn-primary">
                    Download Berita Acara
                </button>
            </div>
        </form>

    </div>
</div>
@endsection