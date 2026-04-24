@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="page-title mb-1">Dashboard</h3>
        <div class="text-muted">Ringkasan absensi peserta catar berdasarkan tanggal ujian.</div>
    </div>

    <form method="GET" action="{{ route('dashboard.index') }}">
        <select name="tanggal_ujian" class="form-select" onchange="this.form.submit()">
            @foreach($tanggalUjianList as $tgl)
                <option value="{{ $tgl }}" {{ $tanggal == $tgl ? 'selected' : '' }}>
                    {{ $tgl }}
                </option>
            @endforeach
        </select>
    </form>
</div>

<div class="row mb-3">
    <div class="col-md-2 mb-2">
        <div class="card summary-card">
            <div class="card-body">
                <h6>Total Peserta</h6>
                <h3>{{ $totalPeserta }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-2 mb-2">
        <div class="card summary-card text-bg-success">
            <div class="card-body">
                <h6>Hadir</h6>
                <h3>{{ $totalHadir }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-2 mb-2">
        <div class="card summary-card text-bg-danger">
            <div class="card-body">
                <h6>Tidak Hadir</h6>
                <h3>{{ $totalTidakHadir }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-2 mb-2">
        <div class="card summary-card text-bg-secondary">
            <div class="card-body">
                <h6>Belum Absen</h6>
                <h3>{{ $totalBelumAbsen }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-2 mb-2">
        <div class="card summary-card text-bg-warning">
            <div class="card-body">
                <h6>Sudah Pulang</h6>
                <h3>{{ $totalSudahPulang }}</h3>
            </div>
        </div>
    </div>

    <div class="col-md-2 mb-2">
        <div class="card summary-card text-bg-light">
            <div class="card-body">
                <h6>Belum Pulang</h6>
                <h3>{{ $totalBelumPulang }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card page-card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-2">
            <strong>Progress Kehadiran</strong>
            <strong>{{ $persenHadir }}%</strong>
        </div>

        <div class="row mb-3">
            <div class="col-md-5 mb-3">
                <div class="card page-card h-100">
                    <div class="card-body">
                        <h5 class="mb-3">Komposisi Absensi</h5>
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-md-7 mb-3">
                <div class="card page-card h-100">
                    <div class="card-body">
                        <h5 class="mb-3">Rekap per Jurusan</h5>
                        <canvas id="jurusanChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="progress" style="height: 24px; border-radius: 999px;">
            <div class="progress-bar bg-success"
                 style="width: {{ $persenHadir }}%;">
                {{ $totalHadir }} / {{ $totalPeserta }}
            </div>
        </div>
    </div>
</div>

<div class="card page-card">
    <div class="card-body table-responsive">
        <h5 class="mb-3">Aktivitas Terakhir</h5>

        <table class="table table-bordered table-striped table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>User</th>
                    <th>Peserta</th>
                    <th>Aksi</th>
                    <th>Status Baru</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentLogs as $log)
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
                        <td>{{ $log->status_baru ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada aktivitas.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
const statusChart = new Chart(document.getElementById('statusChart'), {
    type: 'doughnut',
    data: {
        labels: ['Hadir', 'Tidak Hadir', 'Belum Absen'],
        datasets: [{
            data: [
                {{ $totalHadir }},
                {{ $totalTidakHadir }},
                {{ $totalBelumAbsen }}
            ],
            backgroundColor: ['#198754', '#dc3545', '#6c757d']
        }]
    }
});

const jurusanChart = new Chart(document.getElementById('jurusanChart'), {
    type: 'bar',
    data: {
        labels: @json($jurusanStats->pluck('jurusan')),
        datasets: [
            {
                label: 'Hadir',
                data: @json($jurusanStats->pluck('hadir')),
                backgroundColor: '#198754'
            },
            {
                label: 'Tidak Hadir',
                data: @json($jurusanStats->pluck('tidak_hadir')),
                backgroundColor: '#dc3545'
            },
            {
                label: 'Belum Absen',
                data: @json($jurusanStats->pluck('belum_absen')),
                backgroundColor: '#6c757d'
            }
        ]
    },
    options: {
        responsive: true,
        scales: {
            x: { stacked: true },
            y: { stacked: true, beginAtZero: true }
        }
    }
});
</script>

@endsection