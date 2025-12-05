<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - IoTrack</title>
    <link rel="icon" type="image/png" href="{{ asset('images/IoTrackLogo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --gradient: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
        }

        [data-theme="light"] {
            --bg: #f0f5ff;
            --card: #ffffff;
            --text: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
        }

        [data-theme="dark"] {
            --bg: #0f172a;
            --card: #1e293b;
            --text: #f1f5f9;
            --text-muted: #94a3b8;
            --border: #334155;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            background: var(--bg);
            transition: all 0.3s;
        }

        .login-container { display: flex; width: 100%; min-height: 100vh; }

        .login-left {
            flex: 1;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            background: var(--gradient);
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -30%;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 6s ease-in-out infinite;
        }

        .login-left::after {
            content: '';
            position: absolute;
            bottom: -20%;
            left: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
            animation: float 8s ease-in-out infinite reverse;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(5deg); }
        }

        .login-left-content {
            position: relative;
            z-index: 1;
            text-align: center;
            color: #fff;
            max-width: 420px;
        }

        .login-left-content .logo {
            width: 90px;
            height: 90px;
            padding: 15px;
            background: rgba(255,255,255,0.2);
            border-radius: 24px;
            margin-bottom: 2rem;
            backdrop-filter: blur(10px);
        }

        .login-left-content h1 { font-size: 2.75rem; font-weight: 800; margin-bottom: 1rem; }
        .login-left-content p { font-size: 1.1rem; opacity: 0.9; line-height: 1.7; }

        .login-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: var(--card);
            position: relative;
        }

        .theme-toggle {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.3s;
        }

        .theme-toggle:hover { color: var(--primary); border-color: var(--primary); }

        [data-theme="dark"] .theme-toggle .bi-moon-fill { display: none; }
        [data-theme="light"] .theme-toggle .bi-sun-fill { display: none; }

        .login-form { width: 100%; max-width: 400px; }

        .login-header { text-align: center; margin-bottom: 2.5rem; }
        .login-header .logo { width: 60px; height: 60px; margin-bottom: 1.5rem; }
        .login-header h2 { font-size: 1.75rem; font-weight: 800; color: var(--text); margin-bottom: 0.5rem; }
        .login-header p { color: var(--text-muted); font-size: 0.95rem; }

        .form-label { font-size: 0.875rem; font-weight: 600; color: var(--text); margin-bottom: 0.5rem; }

        .form-control {
            padding: 0.875rem 1rem;
            border: 2px solid var(--border);
            border-radius: 12px;
            font-size: 0.95rem;
            background: var(--card);
            color: var(--text);
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
            background: var(--card);
            color: var(--text);
        }

        .input-group-text {
            background: var(--bg);
            border: 2px solid var(--border);
            border-right: none;
            border-radius: 12px 0 0 12px;
            color: var(--text-muted);
            padding: 0 1rem;
        }

        .input-group .form-control { border-left: none; border-radius: 0 12px 12px 0; }
        .input-group:focus-within .input-group-text { border-color: var(--primary); }

        .btn-login {
            width: 100%;
            padding: 1rem;
            background: var(--gradient);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .btn-login:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4); }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.3s;
        }

        .btn-back:hover { color: var(--primary); }

        .alert { border: none; border-radius: 12px; padding: 1rem 1.25rem; font-weight: 500; }

        @media (min-width: 992px) { .login-left { display: flex; } }
        @media (max-width: 576px) { .login-right { padding: 1.5rem; } .login-header h2 { font-size: 1.5rem; } }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <div class="login-left-content">
                <img src="{{ asset('images/IoTrackLogo.png') }}" alt="IoTrack" class="logo">
                <h1>IoTrack</h1>
                <p>Sistem pelacak kunjungan dan peminjaman peralatan untuk Laboratorium IoT yang modern dan efisien.</p>
            </div>
        </div>

        <div class="login-right">
            <button type="button" class="theme-toggle" id="themeToggle">
                <i class="bi bi-moon-fill"></i>
                <i class="bi bi-sun-fill"></i>
            </button>

            <div class="login-form">
                <div class="login-header">
                    <img src="{{ asset('images/IoTrackLogo.png') }}" alt="IoTrack" class="logo d-lg-none">
                    <h2>Masuk ke Dashboard</h2>
                    <p>Silakan masuk untuk mengelola sistem</p>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger mb-4">
                        <i class="bi bi-exclamation-circle me-2"></i>{{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ route('admin.login.process') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" class="form-control" placeholder="admin@iot.com" value="{{ old('email') }}" required autofocus>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-login mb-4">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Masuk
                    </button>
                    <div class="text-center">
                        <a href="/" class="btn-back"><i class="bi bi-arrow-left"></i> Kembali ke Tap In</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const themeToggle = document.getElementById('themeToggle');
        const html = document.documentElement;
        const savedTheme = localStorage.getItem('theme') || 'light';
        html.setAttribute('data-theme', savedTheme);

        themeToggle.addEventListener('click', function() {
            const newTheme = html.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });
    });
    </script>
</body>
</html>