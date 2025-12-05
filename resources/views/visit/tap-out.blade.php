<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tap Out - IoTrack</title>
    <link rel="icon" type="image/png" href="{{ asset('images/IoTrackLogo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #1e1b4b 0%, #4c1d95 50%, #7c3aed 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 70% 30%, rgba(239, 68, 68, 0.15) 0%, transparent 50%),
                        radial-gradient(circle at 30% 70%, rgba(251, 146, 60, 0.1) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(2%, -2%) rotate(-2deg); }
        }

        .main-card {
            width: 100%;
            max-width: 440px;
            background: rgba(255,255,255,0.95);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 80px rgba(0,0,0,0.4);
            position: relative;
            z-index: 10;
            backdrop-filter: blur(20px);
        }

        .card-header {
            background: linear-gradient(135deg, #ef4444 0%, #f97316 100%);
            color: #fff;
            padding: 2.5rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 100%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
        }

        .card-header .logo {
            width: 70px;
            height: 70px;
            margin-bottom: 1rem;
            filter: drop-shadow(0 4px 12px rgba(0,0,0,0.2));
            position: relative;
        }

        .card-header h1 {
            font-size: 1.75rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .card-header p {
            opacity: 0.9;
            font-size: 0.95rem;
            position: relative;
        }

        .card-body { padding: 2rem; }

        .info-box {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 2px solid #fbbf24;
            border-radius: 16px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .info-box i {
            font-size: 2rem;
            color: #d97706;
            margin-bottom: 0.5rem;
        }

        .info-box p {
            color: #92400e;
            font-size: 0.9rem;
            line-height: 1.6;
            margin: 0;
        }

        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-label i { color: #ef4444; }

        .form-control {
            padding: 0.875rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.3s;
            background: #fff;
        }

        .form-control:focus {
            border-color: #ef4444;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
        }

        .input-group-text {
            background: #fef2f2;
            border: 2px solid #e5e7eb;
            border-right: none;
            border-radius: 12px 0 0 12px;
            color: #ef4444;
            padding: 0 1rem;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
        }

        .input-group:focus-within .input-group-text { border-color: #ef4444; }

        .form-text {
            font-size: 0.8rem;
            color: #9ca3af;
            margin-top: 0.5rem;
        }

        .btn-tapout {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #ef4444 0%, #f97316 100%);
            border: none;
            border-radius: 12px;
            font-weight: 700;
            font-size: 1rem;
            color: #fff;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-tapout:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(239, 68, 68, 0.4);
        }

        .btn-back {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.875rem;
            background: #f3f4f6;
            border: none;
            border-radius: 12px;
            color: #4b5563;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            margin-top: 1rem;
        }

        .btn-back:hover {
            background: #e5e7eb;
            color: #1f2937;
            transform: translateY(-2px);
        }

        .alert {
            border: none;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            font-size: 0.9rem;
            margin-bottom: 1.25rem;
            font-weight: 500;
        }

        .alert-success { background: rgba(16, 185, 129, 0.15); color: #059669; }
        .alert-danger { background: rgba(239, 68, 68, 0.15); color: #dc2626; }

        .time-display {
            position: fixed;
            bottom: 1.5rem;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 0.75rem 1.5rem;
            border-radius: 2rem;
            color: rgba(255,255,255,0.9);
            font-size: 0.9rem;
            font-weight: 500;
            border: 1px solid rgba(255,255,255,0.1);
            z-index: 100;
        }
    </style>
</head>
<body>
    <div class="time-display">
        <i class="bi bi-clock me-2"></i><span id="currentTime"></span>
    </div>

    <div class="main-card">
        <div class="card-header">
            <img src="{{ asset('images/IoTrackLogo.png') }}" alt="IoTrack" class="logo">
            <h1><i class="bi bi-door-open"></i> Tap Out</h1>
            <p>Selesaikan kunjungan Anda</p>
        </div>

        <div class="card-body">
            <div class="info-box">
                <i class="bi bi-info-circle d-block"></i>
                <p>Masukkan NIM Anda untuk mengembalikan barang pinjaman dan menyelesaikan sesi kunjungan di Lab IoT.</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}</div>
            @endif
            @if($errors->any() && !session('error'))
                <div class="alert alert-danger">
                    @foreach($errors->all() as $error)
                        <div><i class="bi bi-exclamation-circle me-1"></i>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('visit.tap-out-process') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="form-label"><i class="bi bi-person-badge"></i> NIM / ID Mahasiswa</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="visitor_id" class="form-control" 
                               placeholder="Contoh: J0404241017" value="{{ old('visitor_id') }}" required autofocus>
                    </div>
                    <div class="form-text">Masukkan NIM yang sama dengan saat Tap In</div>
                </div>

                <button type="submit" class="btn-tapout">
                    <i class="bi bi-box-arrow-right"></i> Tap Out Sekarang
                </button>

                <a href="{{ route('visit.create') }}" class="btn-back">
                    <i class="bi bi-arrow-left"></i> Kembali ke Tap In
                </a>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        function updateTime() {
            const now = new Date();
            document.getElementById('currentTime').textContent = now.toLocaleString('id-ID', {
                weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });
        }
        updateTime();
        setInterval(updateTime, 1000);
    });
    </script>
</body>
</html>