@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="page-title mb-1">Tambah Tahap Ujian</h3>
        <div class="text-muted">Tambahkan jenis/tahap ujian baru.</div>
    </div>

    <a href="{{ route('tahap-ujians.index') }}" class="btn btn-outline-secondary">
        Kembali
    </a>
</div>

<div class="card page-card">
    <div class="card-body">
        <form action="{{ route('tahap-ujians.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-bold">Nama Tahap</label>
                <input type="text"
                       name="nama"
                       class="form-control"
                       value="{{ old('nama') }}"
                       placeholder="Contoh: Tes Kesehatan, Psikotes, Wawancara"
                       required>
                @error('nama')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input"
                       type="checkbox"
                       name="is_active"
                       id="is_active"
                       value="1"
                       checked>
                <label class="form-check-label" for="is_active">
                    Aktif
                </label>
            </div>

            <button class="btn btn-primary">
                Simpan
            </button>
        </form>
    </div>
</div>
@endsection