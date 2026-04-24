<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'SEKOLAH TINGGI ILMU PELAYARAN' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
            color: #1f2937;
        }

        .app-navbar {
            background: linear-gradient(135deg, #0f172a, #1e293b);
            border-bottom: 4px solid #d4af37;
        }

        .navbar-brand-custom {
            color: #ffffff;
            font-weight: 700;
            letter-spacing: .3px;
            text-decoration: none;
        }

        .nav-btn {
            border-radius: 999px;
            padding: 6px 14px;
            font-size: 14px;
            font-weight: 600;
        }

        .nav-btn-outline {
            color: #e5e7eb;
            border: 1px solid rgba(255,255,255,.25);
            background: transparent;
        }

        .nav-btn-outline:hover {
            color: #0f172a;
            background: #d4af37;
            border-color: #d4af37;
        }

        .nav-btn-active {
            color: #0f172a;
            background: #d4af37;
            border: 1px solid #d4af37;
        }

        .user-chip {
            color: #e5e7eb;
            font-size: 13px;
            border-left: 1px solid rgba(255,255,255,.25);
            padding-left: 12px;
        }

        .page-card {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, .06);
        }

        .page-title {
            font-weight: 800;
            color: #0f172a;
        }

        .table thead th {
            background: #f8fafc;
            color: #334155;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: .4px;
        }

        .table tbody tr:hover {
            background: #f8fafc;
        }

        .btn {
            border-radius: 10px;
            font-weight: 600;
        }

        .badge {
            border-radius: 999px;
            padding: 7px 10px;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
        }

        .summary-card {
            border: 0;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(15, 23, 42, .06);
        }

        .summary-card h6 {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: .4px;
            opacity: .85;
        }

        .summary-card h3 {
            font-weight: 800;
            margin-bottom: 0;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<nav class="navbar app-navbar py-3">
    <div class="container-fluid px-4">
        <a class="navbar-brand-custom fs-5" href="{{ route('peserta.kiosk') }}">
            Absensi Catar STIP
        </a>

        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
            @if(auth()->user()->role === 'admin')

                <a href="{{ route('dashboard.index') }}"
                    class="btn nav-btn {{ request()->routeIs('dashboard.*') ? 'nav-btn-active' : 'nav-btn-outline' }}">
                    Dashboard
                </a>

                <a href="{{ route('peserta.index') }}"
                   class="btn nav-btn {{ request()->routeIs('peserta.index') ? 'nav-btn-active' : 'nav-btn-outline' }}">
                    Data Peserta
                </a>

                <a href="{{ route('peserta.laporan') }}"
                   class="btn nav-btn {{ request()->routeIs('peserta.laporan') ? 'nav-btn-active' : 'nav-btn-outline' }}">
                    Laporan
                </a>

                <a href="{{ route('users.index') }}"
                   class="btn nav-btn {{ request()->routeIs('users.*') ? 'nav-btn-active' : 'nav-btn-outline' }}">
                    User
                </a>

                <a href="{{ route('activity-logs.index') }}"
                    class="btn nav-btn {{ request()->routeIs('activity-logs.*') ? 'nav-btn-active' : 'nav-btn-outline' }}">
                    Log Aktivitas
                </a>
            @endif

            <a href="{{ route('peserta.kiosk') }}"
               class="btn nav-btn {{ request()->routeIs('peserta.kiosk') ? 'nav-btn-active' : 'nav-btn-outline' }}">
                Kiosk
            </a>

            <div class="user-chip">
                {{ auth()->user()->name }}
                <span class="badge bg-light text-dark ms-1">{{ ucfirst(auth()->user()->role) }}</span>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button class="btn btn-sm btn-outline-light nav-btn">Logout</button>
            </form>
        </div>
    </div>
</nav>

<main class="container-fluid px-4 py-4">
    @yield('content')
</main>

</body>
</html>