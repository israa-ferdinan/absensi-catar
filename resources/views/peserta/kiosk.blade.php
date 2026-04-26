@extends('layouts.app')

@section('content')

<style>
    .kiosk-hero {
        background: linear-gradient(135deg, #0f172a, #1e293b);
        color: white;
        border-radius: 20px;
        padding: 28px;
        box-shadow: 0 12px 30px rgba(15, 23, 42, .12);
    }

    .kiosk-input-card {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 10px 25px rgba(15, 23, 42, .06);
    }

    .kiosk-search {
        height: 64px;
        font-size: 22px;
        font-weight: 700;
    }

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

    .btn-kiosk-action {
        min-width: 180px;
        height: 56px;
        font-size: 18px;
        font-weight: 800;
    }
</style>

<div class="row justify-content-center">
    <div class="col-lg-9">

        <div class="kiosk-hero text-center mb-4">
            <h2 class="fw-bold mb-2">Mode Kiosk Absensi</h2>
            <p class="mb-0 opacity-75">Pilih tanggal ujian, cari peserta, lalu proses absen masuk atau pulang.</p>
        </div>

        <div class="card kiosk-input-card mb-3">
            <div class="card-body p-3">
                <label class="form-label fw-bold">Tanggal Ujian</label>
                <input type="date"
                       id="kioskTanggal"
                       class="form-control form-control-lg text-center"
                       value="{{ date('Y-m-d') }}">
            </div>
        </div>

        <div id="susulanModeAlert" class="alert alert-warning text-center fw-bold d-none">
            ⚠️ MODE SUSULAN AKTIF — Tanggal yang dipilih berbeda dari tanggal server hari ini.
        </div>

        <div class="row mb-3" id="kioskSummary">
            <div class="col-md-4 mb-2">
                <div class="card summary-card text-bg-success">
                    <div class="card-body text-center">
                        <h6>Hadir</h6>
                        <h3 id="summaryHadir">0</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-2">
                <div class="card summary-card text-bg-danger">
                    <div class="card-body text-center">
                        <h6>Tidak Hadir</h6>
                        <h3 id="summaryTidakHadir">0</h3>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-2">
                <div class="card summary-card text-bg-secondary">
                    <div class="card-body text-center">
                        <h6>Belum Absen</h6>
                        <h3 id="summaryBelumAbsen">0</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="card kiosk-input-card mb-4">
            <div class="card-body p-4">
                <input type="text"
                       id="kioskSearch"
                       class="form-control form-control-lg text-center kiosk-search"
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
const SERVER_DATE = "{{ now()->format('Y-m-d') }}";

searchInput.focus();

let timer = null;

function loadKioskSummary() {
    const tanggal = tanggalInput.value;

    fetch(`/kiosk/summary?tanggal_ujian=${encodeURIComponent(tanggal)}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('summaryHadir').innerText = data.hadir;
        document.getElementById('summaryTidakHadir').innerText = data.tidak_hadir;
        document.getElementById('summaryBelumAbsen').innerText = data.belum_absen;
    });
}

loadKioskSummary();
updateSusulanModeAlert();

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
                let pulangText = '';
                let susulanText = '';

                if (peserta.status_absen === 'hadir') {
                    statusBadge = '<span class="badge bg-success">Hadir</span>';
                    rowClass = 'peserta-hadir';
                } else if (peserta.status_absen === 'tidak_hadir') {
                    statusBadge = '<span class="badge bg-danger">Tidak Hadir</span>';
                    rowClass = 'peserta-tidak-hadir';
                }

                if (peserta.status_pulang === 'pulang') {
                    pulangText = ' | Pulang: Sudah';
                }

                if (tanggalInput.value !== SERVER_DATE) {
                    susulanText = ' | SUSULAN';
                }

                listHtml += `
                    <button type="button"
                            class="list-group-item list-group-item-action btn-pilih-peserta ${rowClass}"
                            data-peserta='${JSON.stringify(peserta)}'>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-start">
                                <div class="fw-bold">
                                    ${peserta.nama}
                                    ${susulanText ? '<span class="badge bg-warning text-dark ms-2">SUSULAN</span>' : ''}
                                </div>
                                <small>
                                    Kode: ${peserta.kode_pendaftar} |
                                    Jurusan: ${peserta.jurusan ?? '-'} |
                                    Kelompok: ${peserta.kelompok ?? '-'} |
                                    Tanggal Asli: ${peserta.tanggal_ujian ?? '-'}
                                    ${pulangText}
                                </small>
                            </div>
                            <div>${statusBadge}</div>
                        </div>
                    </button>
                `;
            });

            resultBox.innerHTML = `
                <div class="card page-card">
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
    let pulangBadge = '<span class="badge bg-secondary fs-6">Belum Pulang</span>';
    let cardClass = 'peserta-belum';

    let susulanBadge = '';

    if (tanggalInput.value !== SERVER_DATE) {
        susulanBadge = `
            <div class="alert alert-warning text-center fw-bold">
                ⚠️ PESERTA SUSULAN<br>
                <small>Tanggal ujian asli: ${peserta.tanggal_ujian}</small>
            </div>
        `;
    }

    let actionButtons = `
        <button class="btn btn-success btn-lg px-5 btn-kiosk-action btn-kiosk-absen"
                data-id="${peserta.id}"
                data-status="hadir">
            Hadir
        </button>

        <button class="btn btn-danger btn-lg px-5 btn-kiosk-action btn-kiosk-absen"
                data-id="${peserta.id}"
                data-status="tidak_hadir">
            Tidak Hadir
        </button>
    `;

    if (peserta.status_absen === 'hadir') {
        statusBadge = '<span class="badge bg-success fs-6">Hadir</span>';
        cardClass = 'peserta-hadir';

        if (peserta.status_pulang === 'pulang') {
            pulangBadge = '<span class="badge bg-success fs-6">Sudah Pulang</span>';
            actionButtons = '<button class="btn btn-secondary btn-lg btn-kiosk-action" disabled>Sudah Pulang</button>';
        } else {
            actionButtons = `
                <button class="btn btn-warning btn-lg px-5 btn-kiosk-action btn-kiosk-pulang"
                        data-id="${peserta.id}">
                    Pulang
                </button>
            `;
        }
    } else if (peserta.status_absen === 'tidak_hadir') {
        statusBadge = '<span class="badge bg-danger fs-6">Tidak Hadir</span>';
        pulangBadge = '<span class="badge bg-secondary fs-6">Tidak Ada Absen Pulang</span>';
        cardClass = 'peserta-tidak-hadir';
        actionButtons = '<button class="btn btn-secondary btn-lg btn-kiosk-action" disabled>Tidak Bisa Pulang</button>';
    }

    resultBox.innerHTML = `
        <div class="card shadow-sm ${cardClass}">
            <div class="card-body p-4 text-center">
                ${susulanBadge}

                <h3 class="fw-bold mb-3">${peserta.nama}</h3>

                <div class="row text-start mb-4">
                    <div class="col-md-6 mb-2"><strong>Kode:</strong> ${peserta.kode_pendaftar}</div>
                    <div class="col-md-6 mb-2"><strong>Jenis Kelamin:</strong> ${peserta.jenis_kelamin ?? '-'}</div>
                    <div class="col-md-6 mb-2"><strong>Jurusan:</strong> ${peserta.jurusan ?? '-'}</div>
                    <div class="col-md-6 mb-2"><strong>Kelompok:</strong> ${peserta.kelompok ?? '-'}</div>
                    <div class="col-md-6 mb-2"><strong>Tanggal Ujian Asli:</strong> ${peserta.tanggal_ujian ?? '-'}</div>
                    <div class="col-md-6 mb-2"><strong>Tanggal Kiosk:</strong> ${tanggalInput.value}</div>
                    <div class="col-md-6 mb-2"><strong>Status Masuk:</strong> ${statusBadge}</div>
                    <div class="col-md-6 mb-2"><strong>Status Pulang:</strong> ${pulangBadge}</div>
                </div>

                <div class="d-flex justify-content-center gap-3 flex-wrap">
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
        body: JSON.stringify({
            status: status,
            tanggal_absen_aktual: tanggalInput.value
        })
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
                ✔ Absensi berhasil disimpan sebagai <strong>${statusText}</strong>.
            </div>
        `;

        searchInput.value = '';
        searchInput.focus();
        loadKioskSummary();
    })
    .catch(() => {
        alert('Terjadi error saat menyimpan.');
        button.disabled = false;
        button.innerText = status === 'hadir' ? 'Hadir' : 'Tidak Hadir';
    });
});

document.addEventListener('click', function(e) {
    if (!e.target.classList.contains('btn-kiosk-pulang')) return;

    const button = e.target;
    const id = button.dataset.id;

    if (!confirm('Yakin simpan absen pulang peserta ini?')) return;

    button.disabled = true;
    button.innerText = 'Menyimpan...';

    fetch(`/peserta/${id}/pulang`, {
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
            alert(data.message ?? 'Gagal menyimpan absen pulang.');
            button.disabled = false;
            button.innerText = 'Pulang';
            return;
        }

        resultBox.innerHTML = `
            <div class="alert alert-warning text-center fs-4">
                ✔ Absen pulang berhasil disimpan.
            </div>
        `;

        searchInput.value = '';
        searchInput.focus();
        loadKioskSummary();
    })
    .catch(() => {
        alert('Terjadi error saat menyimpan absen pulang.');
        button.disabled = false;
        button.innerText = 'Pulang';
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
    loadKioskSummary();
    updateSusulanModeAlert();
});

function updateSusulanModeAlert() {
        const alertBox = document.getElementById('susulanModeAlert');

        if (tanggalInput.value !== SERVER_DATE) {
            alertBox.classList.remove('d-none');
        } else {
            alertBox.classList.add('d-none');
        }
    }

</script>

@endsection