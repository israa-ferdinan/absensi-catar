@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="page-title mb-1">Edit Jadwal Ujian</h3>
        <div class="text-muted">Ubah tahap, tanggal, kelompok, atau status jadwal ujian.</div>
    </div>

    <a href="{{ route('jadwal-ujians.index') }}" class="btn btn-outline-secondary">
        Kembali
    </a>
</div>

<div class="card page-card">
    <div class="card-body">
        <form action="{{ route('jadwal-ujians.update', $jadwalUjian->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label fw-bold">Tahap Ujian</label>
                <select name="tahap_ujian_id" class="form-select" required>
                    <option value="">-- Pilih Tahap --</option>
                    @foreach($tahapUjians as $tahap)
                        <option value="{{ $tahap->id }}" {{ old('tahap_ujian_id', $jadwalUjian->tahap_ujian_id) == $tahap->id ? 'selected' : '' }}>
                            {{ $tahap->nama }}
                        </option>
                    @endforeach
                </select>
                @error('tahap_ujian_id')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Tanggal</label>
                <input type="date"
                       name="tanggal"
                       class="form-control"
                       value="{{ old('tanggal', $jadwalUjian->tanggal) }}"
                       required>
                @error('tanggal')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Kelompok <small class="text-muted">(opsional)</small></label>
                <input type="text"
                       name="kelompok"
                       class="form-control"
                       value="{{ old('kelompok', $jadwalUjian->kelompok) }}"
                       placeholder="Kosongkan jika berlaku untuk semua kelompok">
                @error('kelompok')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input"
                       type="checkbox"
                       name="is_active"
                       id="is_active"
                       value="1"
                       {{ old('is_active', $jadwalUjian->is_active) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">
                    Aktif
                </label>
            </div>

            <button class="btn btn-primary">
                Update
            </button>
        </form>
    </div>
</div>
@endsection