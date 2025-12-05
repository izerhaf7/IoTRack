<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terima Kasih - IoTrack</title>
    <link rel="icon" type="image/png" href="{{ asset('images/IoTrackLogo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #4c1d95 0%, #5b21b6 50%, #7c3aed 100%);
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
            background: radial-gradient(circle at 30% 20%, rgba(139, 92, 246, 0.2) 0%, transparent 50%),
                        radial-gradient(circle at 70% 80%, rgba(167, 139, 250, 0.15) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(-2%, 2%) rotate(2deg); }
        }

        @keyframes thumbsUp {
            0% { transform: scale(0) rotate(-20deg); opacity: 0; }
            50% { transform: scale(1.2) rotate(10deg); }
            100% { transform: scale(1) rotate(0deg); opacity: 1; }
        }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(255,255,255,0.4); }
            50% { box-shadow: 0 0 0 20px rgba(255,255,255,0); }
        }

        .main-card {
            width: 100%;
            max-width: 440px;
            background: rgba(255,255,255,0.95);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 80px rgba(0,0,0,0.3);
            position: relative;
            z-index: 10;
            backdrop-filter: blur(20px);
        }

        .card-header {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: #fff;
            padding: 3rem 2rem;
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

        .success-icon {
            width: 90px;
            height: 90px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            font-size: 2.75rem;
            position: relative;
            animation: pulse 2s ease-in-out infinite;
        }

        .success-icon i {
            animation: thumbsUp 0.6s ease-out forwards;
        }

        .card-header h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            position: relative;
            opacity: 0.95;
        }

        .card-header .name {
            font-size: 2rem;
            font-weight: 800;
            position: relative;
            text-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .card-body {
            padding: 2rem;
        }

        .message-box {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border: 2px solid #86efac;
            border-radius: 16px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .message-box i {
            font-size: 1.5rem;
            color: #16a34a;
            flex-shrink: 0;
            margin-top: 0.125rem;
        }

        .message-box p {
            color: #166534;
            font-size: 0.95rem;
            line-height: 1.6;
            margin: 0;
            font-weight: 500;
        }

        .warning-box {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 2px solid #fca5a5;
            border-radius: 16px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .warning-box i {
            font-size: 1.5rem;
            color: #dc2626;
            flex-shrink: 0;
            margin-top: 0.125rem;
        }

        .warning-box p {
            color: #991b1b;
            font-size: 0.95rem;
            line-height: 1.6;
            margin: 0;
            font-weight: 600;
        }

        .btn-group {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .btn {
            flex: 1;
            min-width: 140px;
            padding: 1rem 1.25rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none;
            color: #fff;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(99, 102, 241, 0.4);
            color: #fff;
        }

        .btn-outline {
            background: #fff;
            border: 2px solid #e5e7eb;
            color: #4b5563;
        }

        .btn-outline:hover {
            border-color: #ef4444;
            color: #ef4444;
            background: rgba(239, 68, 68, 0.05);
            transform: translateY(-2px);
        }

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
            border: 1px solid rgba(255,255,255,0.15);
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
            <div class="success-icon">
                <i class="bi bi-hand-thumbs-up"></i>
            </div>
            <h1>Terima Kasih</h1>
            <div class="name">{{ $name }}</div>
        </div>

        <div class="card-body">
            <div class="message-box">
                <i class="bi bi-check-circle-fill"></i>
                <p>Kunjungan Anda ke Lab IoT sudah tercatat dengan baik. Sampai jumpa lagi!</p>
            </div>

            <div class="warning-box">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <p>Pastikan area kerja tetap rapi sebelum meninggalkan lab.</p>
            </div>

            <div class="btn-group">
                <a href="{{ route('visit.create') }}" class="btn btn-primary">
                    <i class="bi bi-box-arrow-in-right"></i> Tap In Lagi
                </a>
                <a href="{{ route('visit.tap-out') }}" class="btn btn-outline">
                    <i class="bi bi-box-arrow-right"></i> Tap Out Lain
                </a>
            </div>
        </div>
    </div>

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
