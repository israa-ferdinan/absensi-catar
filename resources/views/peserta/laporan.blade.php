@extends('layouts.app')

@section('content')
        <h3>Laporan Absensi Catar</h3>
    </div>
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card text-bg-success">
                <div class="card-body">
                    <h6>Total Hadir</h6>
                    <h3>{{ $totalHadir }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-danger">
                <div class="card-body">
                    <h6>Total Tidak Hadir</h6>
                    <h3>{{ $totalTidakHadir }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-bg-secondary">
                <div class="card-body">
                    <h6>Belum Absen</h6>
                    <h3>{{ $totalBelumAbsen }}</h3>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('peserta.laporan') }}" class="row g-2">
                <div class="col-md-3">
                    <input type="date" name="tanggal_ujian" class="form-control"
                           value="{{ request('tanggal_ujian') }}">
                </div>
                <div class="col-md-3">
                    <select name="jurusan" class="form-select">
                        <option value="">Semua Jurusan</option>
                        @foreach ($jurusans as $jurusan)
                            <option value="{{ $jurusan }}" {{ request('jurusan') == $jurusan ? 'selected' : '' }}>
                                {{ $jurusan }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="kelompok" class="form-select">
                        <option value="">Semua Kelompok</option>
                        @foreach ($kelompoks as $kelompok)
                            <option value="{{ $kelompok }}" {{ request('kelompok') == $kelompok ? 'selected' : '' }}>
                                {{ $kelompok }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status_absen" class="form-select">
                        <option value="">Semua Status</option>
                        <option value="hadir" {{ request('status_absen') == 'hadir' ? 'selected' : '' }}>Hadir</option>
                        <option value="tidak_hadir" {{ request('status_absen') == 'tidak_hadir' ? 'selected' : '' }}>Tidak Hadir</option>
                        <option value="belum" {{ request('status_absen') == 'belum' ? 'selected' : '' }}>Belum Absen</option>
                    </select>
                </div>
                <div class="col-md-12">
                    <button class="btn btn-primary">Filter</button>
                    <a href="{{ route('peserta.laporan') }}" class="btn btn-outline-secondary">Reset</a>
                    <a href="{{ route('peserta.export', request()->query()) }}" class="btn btn-success">Export Excel</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>JK</th>
                        <th>Jurusan</th>
                        <th>Kelompok</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Waktu Absen</th>
                        <th>Waktu Pulang</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pesertas as $peserta)
                        <tr>
                            <td>{{ $loop->iteration + ($pesertas->currentPage() - 1) * $pesertas->perPage() }}</td>
                            <td>{{ $peserta->kode_pendaftar }}</td>
                            <td>{{ $peserta->nama }}</td>
                            <td>{{ $peserta->jenis_kelamin }}</td>
                            <td>{{ $peserta->jurusan }}</td>
                            <td>{{ $peserta->kelompok }}</td>
                            <td>{{ $peserta->tanggal_ujian }}</td>
                            <td>
                                @if ($peserta->status_absen === 'hadir')
                                    <span class="badge bg-success">Hadir</span>
                                @elseif ($peserta->status_absen === 'tidak_hadir')
                                    <span class="badge bg-danger">Tidak Hadir</span>
                                @else
                                    <span class="badge bg-secondary">Belum Absen</span>
                                @endif
                            </td>
                            <td>{{ $peserta->waktu_absen }}</td>
                            <td>{{ $peserta->waktu_pulang ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">Data tidak ditemukan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3 d-flex justify-content-center">
                {{ $pesertas->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection