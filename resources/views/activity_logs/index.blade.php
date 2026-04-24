@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="page-title mb-1">Log Aktivitas</h3>
        <div class="text-muted">Riwayat aktivitas absensi oleh admin/operator.</div>
    </div>
</div>

<div class="card page-card">
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>User</th>
                    <th>Peserta</th>
                    <th>Aksi</th>
                    <th>Status Lama</th>
                    <th>Status Baru</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at }}</td>
                        <td>{{ $log->user->name ?? '-' }}</td>
                        <td>
                            {{ $log->peserta->nama ?? '-' }}
                            <br>
                            <small class="text-muted">{{ $log->peserta->kode_pendaftar ?? '-' }}</small>
                        </td>
                        <td>
                            @if($log->aksi === 'absen_masuk')
                                <span class="badge bg-success">Absen Masuk</span>
                            @elseif($log->aksi === 'absen_pulang')
                                <span class="badge bg-warning text-dark">Absen Pulang</span>
                            @elseif($log->aksi === 'reset_absensi')
                                <span class="badge bg-danger">Reset</span>
                            @else
                                <span class="badge bg-secondary">{{ $log->aksi }}</span>
                            @endif
                        </td>
                        <td>{{ $log->status_lama ?? '-' }}</td>
                        <td>{{ $log->status_baru ?? '-' }}</td>
                        <td>{{ $log->keterangan ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">Belum ada log aktivitas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-3 d-flex justify-content-center">
            {{ $logs->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection