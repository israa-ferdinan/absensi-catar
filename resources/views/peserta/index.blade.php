@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="page-title mb-1">Data Peserta Catar</h3>
            <div class="text-muted">Kelola data peserta, upload Excel, dan proses absensi.</div>
        </div>
    </div>

    {{-- UPLOAD --}}
    <div class="card page-card mb-3">
        <div class="card-body">
            <form action="{{ route('peserta.import') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <label class="form-label fw-bold">Upload Data Catar</label>

                <div class="input-group">
                    <input type="file" name="file" class="form-control" required>
                    <button class="btn btn-success">Upload Excel</button>
                </div>

                @error('file')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </form>
        </div>
    </div>

    {{-- SEARCH --}}
    <div class="card page-card mb-3">
        <div class="card-body">
            <input type="text" id="searchInput" class="form-control form-control-lg"
                placeholder="Cari nama atau kode pendaftar..."
                value="{{ $search }}">
        </div>
    </div>

    {{-- TABLE --}}
    <div class="card page-card" id="tableContainer">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>JK</th>
                        <th>Jurusan</th>
                        <th>Kelompok</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                        <th>Status</th>
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
                            {{-- AKSI --}}
                            <td id="aksi-{{ $peserta->id }}">
                                @if(!$peserta->status_absen)
                                    <button class="btn btn-success btn-sm btn-absen"
                                            data-id="{{ $peserta->id }}"
                                            data-status="hadir">
                                        Hadir
                                    </button>

                                    <button class="btn btn-danger btn-sm btn-absen"
                                            data-id="{{ $peserta->id }}"
                                            data-status="tidak_hadir">
                                        Tidak Hadir
                                    </button>
                                @else
                                    <button class="btn btn-warning btn-sm btn-reset"
                                            data-id="{{ $peserta->id }}">
                                        Reset
                                    </button>
                                @endif
                            </td>
                            {{-- STATUS --}}
                            <td id="status-{{ $peserta->id }}">
                                @if ($peserta->status_absen === 'hadir')
                                    <span class="badge bg-success">Hadir</span>
                                @elseif ($peserta->status_absen === 'tidak_hadir')
                                    <span class="badge bg-danger">Tidak Hadir</span>
                                @else
                                    <span class="badge bg-secondary">Belum Absen</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Data peserta belum ada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- PAGINATION --}}
            <div class="mt-3 d-flex justify-content-center">
                {{ $pesertas->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>
</div>
<script>
let searchTimer = null;

document.getElementById('searchInput').addEventListener('keyup', function () {
    clearTimeout(searchTimer);

    const keyword = this.value;

    searchTimer = setTimeout(() => {
        fetch(`/peserta?search=${encodeURIComponent(keyword)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newTable = doc.querySelector('#tableContainer');

            document.querySelector('#tableContainer').innerHTML = newTable.innerHTML;
        });
    }, 300);
});
</script>
<script>
document.addEventListener('click', function (e) {
    if (!e.target.classList.contains('btn-absen')) return;

    const button = e.target;
    const id = button.dataset.id;
    const status = button.dataset.status;

    button.disabled = true;
    button.innerText = 'Menyimpan...';

    fetch(`/peserta/${id}/absen`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            alert('Gagal menyimpan absensi.');
            return;
        }

        const statusCell = document.getElementById(`status-${id}`);
        const aksiCell = document.getElementById(`aksi-${id}`);

        if (data.status === 'hadir') {
            statusCell.innerHTML = '<span class="badge bg-success">Hadir</span>';
        } else {
            statusCell.innerHTML = '<span class="badge bg-danger">Tidak Hadir</span>';
        }

        aksiCell.innerHTML = `
            <button class="btn btn-secondary btn-sm" disabled>
                Sudah Diabsen
            </button>
        `;
    })
    .catch(() => {
        alert('Terjadi error saat menyimpan absensi.');
        button.disabled = false;
        button.innerText = status === 'hadir' ? 'Hadir' : 'Tidak Hadir';
    });
    });
    </script>
    <script>
    document.addEventListener('click', function (e) {
        if (!e.target.classList.contains('btn-reset')) return;

        if (!confirm('Yakin mau reset absensi?')) return;

        const button = e.target;
        const id = button.dataset.id;

        button.disabled = true;
        button.innerText = 'Reset...';

        fetch(`/peserta/${id}/reset`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert('Gagal reset.');
                return;
            }

            const statusCell = document.getElementById(`status-${id}`);
            const aksiCell = document.getElementById(`aksi-${id}`);

            statusCell.innerHTML = '<span class="badge bg-secondary">Belum Absen</span>';

            aksiCell.innerHTML = `
                <button class="btn btn-success btn-sm btn-absen" data-id="${id}" data-status="hadir">Hadir</button>
                <button class="btn btn-danger btn-sm btn-absen" data-id="${id}" data-status="tidak_hadir">Tidak Hadir</button>
            `;
        })
        .catch(() => {
            alert('Error saat reset');
            button.disabled = false;
            button.innerText = 'Reset';
        });
    });
    </script>
@endsection
