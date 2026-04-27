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
                <label class="form-label fw-bold">Tahap Ujian</label>
                <select id="tahapUjian" class="form-select form-select-lg text-center">
                    @foreach($tahapUjians as $tahap)
                        <option value="{{ $tahap->id }}">{{ $tahap->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="card kiosk-input-card mb-3">
            <div class="card-body p-3">
                <label class="form-label fw-bold">Tanggal Ujian</label>
                <select id="kioskTanggal" class="form-select form-select-lg text-center"></select>
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
    const tahapInput = document.getElementById('tahapUjian');

    const SERVER_DATE = "{{ now()->format('Y-m-d') }}";

    searchInput.focus();

    let timer = null;

    function selectedTanggal() {
        return tanggalInput.value;
    }

    function isModeSusulan() {
        return selectedTanggal() !== SERVER_DATE;
    }

    function updateSusulanModeAlert() {
        const alertBox = document.getElementById('susulanModeAlert');

        if (isModeSusulan()) {
            alertBox.classList.remove('d-none');
        } else {
            alertBox.classList.add('d-none');
        }
    }

    function loadJadwal() {
        const tahapId = tahapInput.value;

        fetch(`/jadwal-by-tahap?tahap_ujian_id=${encodeURIComponent(tahapId)}`)
            .then(res => res.json())
            .then(data => {
                tanggalInput.innerHTML = '';

                if (data.length === 0) {
                    tanggalInput.innerHTML = '<option value="">Tidak ada jadwal</option>';
                    resultBox.innerHTML = '';
                    loadKioskSummary();
                    updateSusulanModeAlert();
                    return;
                }

                data.forEach(j => {
                    const opt = document.createElement('option');

                    opt.value = j.id;
                    opt.dataset.tanggal = j.tanggal;
                    opt.dataset.kelompok = j.kelompok ?? '';

                    opt.text = j.tanggal + (j.kelompok ? ` - Kelompok ${j.kelompok}` : ' - Semua Kelompok');

                    tanggalInput.appendChild(opt);
                });

                resultBox.innerHTML = '';
                searchInput.value = '';
                searchInput.focus();

                loadKioskSummary();
                updateSusulanModeAlert();
            });
    }

    function selectedTanggal() {
    return tanggalInput.options[tanggalInput.selectedIndex]?.dataset.tanggal ?? '';
}

    function selectedJadwalId() {
        return tanggalInput.value;
    }

    function loadKioskSummary() {
        const tanggal = selectedTanggal();

        if (!tanggal) {
            document.getElementById('summaryHadir').innerText = 0;
            document.getElementById('summaryTidakHadir').innerText = 0;
            document.getElementById('summaryBelumAbsen').innerText = 0;
            return;
        }

        fetch(`/kiosk/summary?tanggal_ujian=${encodeURIComponent(selectedTanggal())}&tahap_ujian_id=${encodeURIComponent(tahapInput.value)}&jadwal_ujian_id=${encodeURIComponent(selectedJadwalId())}`, {
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

    tahapInput.addEventListener('change', function () {
        loadJadwal();
    });

    tanggalInput.addEventListener('change', function () {
        resultBox.innerHTML = '';
        searchInput.value = '';
        searchInput.focus();
        loadKioskSummary();
        updateSusulanModeAlert();
    });

    searchInput.addEventListener('keyup', function () {
        clearTimeout(timer);

        const keyword = this.value.trim();
        const tanggal = selectedTanggal();

        if (!tanggal) {
            resultBox.innerHTML = `
                <div class="alert alert-warning text-center">
                    Jadwal ujian belum tersedia.
                </div>
            `;
            return;
        }

        if (keyword.length < 2) {
            resultBox.innerHTML = '';
            return;
        }

        timer = setTimeout(() => {
            fetch(`/kiosk/search?search=${encodeURIComponent(keyword)}&tanggal_ujian=${encodeURIComponent(selectedTanggal())}&tahap_ujian_id=${encodeURIComponent(tahapInput.value)}&jadwal_ujian_id=${encodeURIComponent(selectedJadwalId())}`, {
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

                let listHtml = '';

                data.pesertas.forEach(peserta => {
                    let statusBadge = '<span class="badge bg-secondary">Belum Absen</span>';
                    let rowClass = 'peserta-belum';
                    let pulangText = '';
                    let susulanBadge = '';

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

                    if (isModeSusulan()) {
                        susulanBadge = '<span class="badge bg-warning text-dark ms-2">SUSULAN</span>';
                    }

                    listHtml += `
                        <button type="button"
                                class="list-group-item list-group-item-action btn-pilih-peserta ${rowClass}"
                                data-peserta='${JSON.stringify(peserta)}'>
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-start">
                                    <div class="fw-bold">
                                        ${peserta.nama}
                                        ${susulanBadge}
                                    </div>
                                    <small>
                                        Kode: ${peserta.kode_pendaftar} |
                                        Jurusan: ${peserta.jurusan ?? '-'} |
                                        Kelompok: ${peserta.kelompok ?? '-'} |
                                        Tanggal Jadwal: ${selectedTanggal()}
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

        if (isModeSusulan()) {
            susulanBadge = `
                <div class="alert alert-warning text-center fw-bold">
                    ⚠️ PESERTA SUSULAN<br>
                    <small>Tanggal server hari ini: ${SERVER_DATE}</small><br>
                    <small>Tanggal jadwal dipilih: ${selectedTanggal()}</small>
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
                        <div class="col-md-6 mb-2"><strong>Tahap:</strong> ${tahapInput.options[tahapInput.selectedIndex].text}</div>
                        <div class="col-md-6 mb-2"><strong>Tanggal Jadwal:</strong> ${selectedTanggal()}</div>
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
                tanggal_absen_aktual: selectedTanggal(),
                tahap_ujian_id: tahapInput.value,
                jadwal_ujian_id: selectedJadwalId()
            })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message ?? 'Gagal menyimpan absensi.'
                });
                button.disabled = false;
                button.innerText = status === 'hadir' ? 'Hadir' : 'Tidak Hadir';
                return;
            }

            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Absensi berhasil disimpan.',
                timer: 1500,
                showConfirmButton: false
            });

            resultBox.innerHTML = '';
            searchInput.value = '';
            searchInput.focus();
            loadKioskSummary();
        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Terjadi error saat menyimpan.'
            });

            button.disabled = false;
            button.innerText = status === 'hadir' ? 'Hadir' : 'Tidak Hadir';
        });
    });

    document.addEventListener('click', function(e) {
        if (!e.target.classList.contains('btn-kiosk-pulang')) return;

        const button = e.target;
        const id = button.dataset.id;

        Swal.fire({
            title: 'Konfirmasi Absen Pulang',
            text: 'Yakin simpan absen pulang peserta ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Simpan',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (!result.isConfirmed) return;

            button.disabled = true;
            button.innerText = 'Menyimpan...';

            fetch(`/peserta/${id}/pulang`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    tahap_ujian_id: tahapInput.value,
                    tanggal_jadwal: selectedTanggal()
                })
            })
            .then(res => res.json())
            .then(data => {
                if (!data.success) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: data.message ?? 'Gagal menyimpan absen pulang.'
                    });

                    button.disabled = false;
                    button.innerText = 'Pulang';
                    return;
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Absen pulang berhasil disimpan.',
                    timer: 1500,
                    showConfirmButton: false
                });

                resultBox.innerHTML = '';
                searchInput.value = '';
                searchInput.focus();
                loadKioskSummary();
            })
            .catch(() => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Terjadi error saat menyimpan absen pulang.'
                });

                button.disabled = false;
                button.innerText = 'Pulang';
            });
        });
    });

    document.addEventListener('click', function(e) {
        if (e.target.id !== 'btnBackToSearch') return;

        searchInput.dispatchEvent(new Event('keyup'));
    });

    document.addEventListener('DOMContentLoaded', function () {
        loadJadwal();
    });
    </script>

@endsection