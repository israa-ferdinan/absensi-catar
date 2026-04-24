@extends('layouts.app')

@section('content')

<style>
    .peserta-hadir {
        background-color: #e8f7ee !important;
        border-left: 6px solid #198754 !important;
    }

    .peserta-tidak-hadir {
        background-color: #fdeaea !important;
        border-left: 6px solid #dc3545 !important;
    }

    .peserta-belum {
        background-color: #ffffff !important;
        border-left: 6px solid #adb5bd !important;
    }
</style>

<div class="row justify-content-center">
    <div class="col-lg-8">

        <div class="text-center mb-4">
            <h2 class="fw-bold">Mode Kiosk Absensi</h2>
            <p class="text-muted">Cari peserta berdasarkan nama atau kode pendaftar</p>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body p-3">
                <label class="form-label fw-bold">Tanggal Ujian</label>
                <input type="date"
                       id="kioskTanggal"
                       class="form-control form-control-lg text-center"
                       value="{{ date('Y-m-d') }}">
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-body p-4">
                <input type="text"
                       id="kioskSearch"
                       class="form-control form-control-lg text-center"
                       placeholder="Ketik kode pendaftar atau nama peserta..."
                       autocomplete="off">
            </div>
        </div>

        <div id="kioskResult"></div>

    </div>
</div>

<script>
const searchInput = document.getElementById('kioskSearch');
const resultBox = document.getElementById('kioskResult');
const tanggalInput = document.getElementById('kioskTanggal');

searchInput.focus();

let timer = null;

searchInput.addEventListener('keyup', function () {
    clearTimeout(timer);

    const keyword = this.value.trim();
    const tanggal = tanggalInput.value;

    if (keyword.length < 2) {
        resultBox.innerHTML = '';
        return;
    }

    timer = setTimeout(() => {
        fetch(`/kiosk/search?search=${encodeURIComponent(keyword)}&tanggal_ujian=${encodeURIComponent(tanggal)}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (!data.found) {
                resultBox.innerHTML = `
                    <div class="alert alert-warning text-center">
                        Peserta tidak ditemukan.
                    </div>
                `;
                return;
            }

            const pesertas = data.pesertas;
            let listHtml = '';

            pesertas.forEach(peserta => {
                let statusBadge = '<span class="badge bg-secondary">Belum Absen</span>';
                let rowClass = 'peserta-belum';

                if (peserta.status_absen === 'hadir') {
                    statusBadge = '<span class="badge bg-success">Hadir</span>';
                    rowClass = 'peserta-hadir';
                } else if (peserta.status_absen === 'tidak_hadir') {
                    statusBadge = '<span class="badge bg-danger">Tidak Hadir</span>';
                    rowClass = 'peserta-tidak-hadir';
                }

                listHtml += `
                    <button type="button"
                            class="list-group-item list-group-item-action btn-pilih-peserta ${rowClass}"
                            data-peserta='${JSON.stringify(peserta)}'>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-start">
                                <div class="fw-bold">${peserta.nama}</div>
                                <small>
                                    Kode: ${peserta.kode_pendaftar} |
                                    Jurusan: ${peserta.jurusan ?? '-'} |
                                    Kelompok: ${peserta.kelompok ?? '-'} |
                                    Tanggal: ${peserta.tanggal_ujian ?? '-'}
                                </small>
                            </div>
                            <div>${statusBadge}</div>
                        </div>
                    </button>
                `;
            });

            resultBox.innerHTML = `
                <div class="card shadow-sm">
                    <div class="card-body p-3">
                        <h5 class="mb-3">Pilih Peserta</h5>
                        <div class="list-group">
                            ${listHtml}
                        </div>
                    </div>
                </div>
            `;
        })
        .catch(() => {
            resultBox.innerHTML = `
                <div class="alert alert-danger text-center">
                    Terjadi error saat mencari peserta.
                </div>
            `;
        });
    }, 300);
});

document.addEventListener('click', function(e) {
    const item = e.target.closest('.btn-pilih-peserta');
    if (!item) return;

    const peserta = JSON.parse(item.dataset.peserta);

    let statusBadge = '<span class="badge bg-secondary fs-6">Belum Absen</span>';
    let cardClass = 'peserta-belum';
    let actionButtons = `
        <button class="btn btn-success btn-lg px-5 btn-kiosk-absen" data-id="${peserta.id}" data-status="hadir">
            Hadir
        </button>
        <button class="btn btn-danger btn-lg px-5 btn-kiosk-absen" data-id="${peserta.id}" data-status="tidak_hadir">
            Tidak Hadir
        </button>
    `;

    if (peserta.status_absen === 'hadir') {
        statusBadge = '<span class="badge bg-success fs-6">Hadir</span>';
        cardClass = 'peserta-hadir';
        actionButtons = '<button class="btn btn-secondary btn-lg" disabled>Sudah Diabsen</button>';
    } else if (peserta.status_absen === 'tidak_hadir') {
        statusBadge = '<span class="badge bg-danger fs-6">Tidak Hadir</span>';
        cardClass = 'peserta-tidak-hadir';
        actionButtons = '<button class="btn btn-secondary btn-lg" disabled>Sudah Diabsen</button>';
    }

    resultBox.innerHTML = `
        <div class="card shadow-sm ${cardClass}">
            <div class="card-body p-4 text-center">
                <h3 class="fw-bold mb-3">${peserta.nama}</h3>

                <div class="row text-start mb-4">
                    <div class="col-md-6 mb-2"><strong>Kode:</strong> ${peserta.kode_pendaftar}</div>
                    <div class="col-md-6 mb-2"><strong>Jenis Kelamin:</strong> ${peserta.jenis_kelamin ?? '-'}</div>
                    <div class="col-md-6 mb-2"><strong>Jurusan:</strong> ${peserta.jurusan ?? '-'}</div>
                    <div class="col-md-6 mb-2"><strong>Kelompok:</strong> ${peserta.kelompok ?? '-'}</div>
                    <div class="col-md-6 mb-2"><strong>Tanggal Ujian:</strong> ${peserta.tanggal_ujian ?? '-'}</div>
                    <div class="col-md-6 mb-2"><strong>Status:</strong> ${statusBadge}</div>
                </div>

                <div class="d-flex justify-content-center gap-3">
                    ${actionButtons}
                </div>

                <button class="btn btn-outline-secondary mt-3" id="btnBackToSearch">
                    Kembali ke hasil pencarian
                </button>
            </div>
        </div>
    `;
});

document.addEventListener('click', function(e) {
    if (!e.target.classList.contains('btn-kiosk-absen')) return;

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
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            alert('Gagal menyimpan absensi.');
            return;
        }

        const alertClass = data.status === 'hadir' ? 'alert-success' : 'alert-danger';
        const statusText = data.status === 'hadir' ? 'HADIR' : 'TIDAK HADIR';

        resultBox.innerHTML = `
            <div class="alert ${alertClass} text-center fs-4">
                ✔ Absensi berhasil disimpan sebagai <strong>${statusText}</strong>
            </div>
        `;

        searchInput.value = '';
        searchInput.focus();
    })
    .catch(() => {
        alert('Terjadi error saat menyimpan.');
        button.disabled = false;
        button.innerText = status === 'hadir' ? 'Hadir' : 'Tidak Hadir';
    });
});

document.addEventListener('click', function(e) {
    if (e.target.id !== 'btnBackToSearch') return;

    searchInput.dispatchEvent(new Event('keyup'));
});

tanggalInput.addEventListener('change', function () {
    resultBox.innerHTML = '';
    searchInput.value = '';
    searchInput.focus();
});
</script>

@endsection