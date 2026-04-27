@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="page-title mb-1">Jadwal Ujian</h3>
        <div class="text-muted">Kelola tanggal pelaksanaan ujian per tahap.</div>
    </div>

    <a href="{{ route('jadwal-ujians.create') }}" class="btn btn-primary">
        Tambah Jadwal
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card page-card">
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tahap Ujian</th>
                    <th>Tanggal</th>
                    <th>Kelompok</th>
                    <th>Status</th>
                    <th width="180">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jadwals as $jadwal)
                    <tr>
                        <td>{{ $loop->iteration + ($jadwals->currentPage() - 1) * $jadwals->perPage() }}</td>
                        <td>{{ $jadwal->tahapUjian->nama ?? '-' }}</td>
                        <td>{{ $jadwal->tanggal }}</td>
                        <td>{{ $jadwal->kelompok ?? 'Semua Kelompok' }}</td>
                        <td>
                            @if($jadwal->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('jadwal-ujians.edit', $jadwal->id) }}" class="btn btn-warning btn-sm">
                                Edit
                            </a>

                            <form action="{{ route('jadwal-ujians.destroy', $jadwal->id) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Yakin hapus jadwal ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Belum ada jadwal ujian.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-3 d-flex justify-content-center">
            {{ $jadwals->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection