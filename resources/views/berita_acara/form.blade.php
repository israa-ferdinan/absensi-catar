@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="page-title mb-1">Berita Acara</h3>
        <div class="text-muted">Generate dokumen Berita Acara berdasarkan tanggal pelaksanaan ujian.</div>
    </div>
</div>

<div class="card page-card">
    <div class="card-body">
        <form method="POST" action="{{ route('berita-acara.download') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-bold">Tanggal Pelaksanaan</label>
                <input type="date"
                       name="tanggal"
                       class="form-control"
                       required>
                @error('tanggal')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Laporan Kejadian Penyelenggara Tes</label>
                <textarea name="laporan_penyelenggara"
                          class="form-control"
                          rows="5"
                          placeholder="Kosongkan jika tidak ada laporan kejadian. Sistem akan mengisi NIHIL.">{{ old('laporan_penyelenggara') }}</textarea>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Laporan Kejadian Panitia Penyelenggara</label>
                <textarea name="laporan_panitia"
                          class="form-control"
                          rows="5"
                          placeholder="Kosongkan jika tidak ada laporan kejadian. Sistem akan mengisi NIHIL.">{{ old('laporan_panitia') }}</textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('dashboard.index') }}" class="btn btn-outline-secondary">
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