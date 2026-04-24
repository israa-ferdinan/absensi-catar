<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Absensi Catar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-card {
            width: 100%;
            max-width: 430px;
            border: 0;
            border-radius: 22px;
            box-shadow: 0 20px 45px rgba(0,0,0,.25);
            overflow: hidden;
        }

        .login-header {
            background: #d4af37;
            color: #0f172a;
            padding: 24px;
            text-align: center;
        }

        .login-header h3 {
            font-weight: 800;
            margin-bottom: 4px;
        }

        .login-body {
            padding: 28px;
            background: #ffffff;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px;
        }

        .btn-login {
            background: #0f172a;
            color: #ffffff;
            border-radius: 12px;
            padding: 12px;
            font-weight: 700;
        }

        .btn-login:hover {
            background: #1e293b;
            color: #ffffff;
        }
    </style>
</head>
<body>

<div class="card login-card">
    <div class="login-header">
        <h3>Absensi Catar</h3>
        <div>Sistem Absensi Peserta Catar</div>
    </div>

    <div class="login-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                Username atau password salah.
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label fw-bold">Username</label>
                <input type="text"
                       name="username"
                       class="form-control"
                       value="{{ old('username') }}"
                       required
                       autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Password</label>
                <input type="password"
                       name="password"
                       class="form-control"
                       required>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember">
                    Ingat saya
                </label>
            </div>

            <button type="submit" class="btn btn-login w-100">
                Login
            </button>
        </form>
    </div>
</div>

</body>
</html>