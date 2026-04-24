<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Absensi Catar' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg bg-white border-bottom shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('peserta.index') }}">
            Absensi Catar
        </a>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('peserta.index') }}" class="btn btn-sm {{ request()->routeIs('peserta.index') ? 'btn-primary' : 'btn-outline-primary' }}">
                Data Peserta
            </a>

            <a href="{{ route('peserta.laporan') }}" class="btn btn-sm {{ request()->routeIs('peserta.laporan') ? 'btn-primary' : 'btn-outline-primary' }}">
                Laporan
            </a>

            <a href="{{ route('peserta.kiosk') }}" class="btn btn-sm {{ request()->routeIs('peserta.kiosk') ? 'btn-primary' : 'btn-outline-primary' }}">
                Kiosk
            </a>

            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button class="btn btn-danger btn-sm">
                    🔓 Logout
                </button>
            </form>
        </div>
    </div>
</nav>

<main class="container py-4">
    @yield('content')
</main>

</body>
</html>