<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tap In - IoTrack</title>
    <link rel="icon" type="image/png" href="{{ asset('images/IoTrackLogo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #312e81 100%);
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
            background: radial-gradient(circle at 30% 20%, rgba(99, 102, 241, 0.15) 0%, transparent 50%),
                        radial-gradient(circle at 70% 80%, rgba(139, 92, 246, 0.1) 0%, transparent 50%);
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(-2%, 2%) rotate(2deg); }
        }

        .admin-link {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            padding: 0.625rem 1.25rem;
            background: rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.8);
            border-radius: 2rem;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.1);
            z-index: 100;
        }

        .admin-link:hover {
            background: rgba(255,255,255,0.2);
            color: #fff;
            transform: translateY(-2px);
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
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
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
        }

        .card-header p {
            opacity: 0.9;
            font-size: 0.95rem;
            position: relative;
        }

        .card-body { padding: 2rem; }

        .form-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-label i { color: #6366f1; }

        .form-control, .form-select {
            padding: 0.875rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: all 0.3s;
            background: #fff;
        }

        .form-control:focus, .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
        }

        .input-group .form-control { border-right: none; border-radius: 12px 0 0 12px; }

        .input-group .btn-scan {
            border: 2px solid #e5e7eb;
            border-left: none;
            border-radius: 0 12px 12px 0;
            background: #f9fafb;
            color: #6b7280;
            padding: 0 1rem;
            transition: all 0.3s;
        }

        .input-group .btn-scan:hover {
            background: #6366f1;
            border-color: #6366f1;
            color: #fff;
        }

        .input-group:focus-within .btn-scan { border-color: #6366f1; }

        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
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

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(99, 102, 241, 0.4);
        }

        .borrow-section {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            border: 2px solid #bae6fd;
            border-radius: 16px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .borrow-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #bae6fd;
            color: #0369a1;
            font-weight: 700;
            font-size: 0.9rem;
        }

        .tap-out-section {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #f3f4f6;
        }

        .tap-out-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #ef4444;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.75rem 1.5rem;
            border-radius: 2rem;
            transition: all 0.3s;
            background: rgba(239, 68, 68, 0.1);
        }

        .tap-out-link:hover {
            background: #ef4444;
            color: #fff;
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

        #qrScannerContainer { margin-bottom: 1rem; animation: slideIn 0.3s ease; }

        .qr-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.875rem 1rem;
            background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            color: #fff;
            border-radius: 12px 12px 0 0;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .qr-close {
            background: rgba(255,255,255,0.2);
            border: none;
            color: #fff;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .qr-close:hover { background: rgba(255,255,255,0.3); }

        #qrReader { border-radius: 0 0 12px 12px; overflow: hidden; }

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
    <a href="{{ route('login') }}" class="admin-link">
        <i class="bi bi-shield-lock me-2"></i>Admin
    </a>

    <div class="time-display">
        <i class="bi bi-clock me-2"></i><span id="currentTime"></span>
    </div>

    <div class="main-card">
        <div class="card-header">
            <img src="{{ asset('images/IoTrackLogo.png') }}" alt="IoTrack" class="logo">
            <h1>Selamat Datang!</h1>
            <p>Silakan isi data untuk masuk ke Lab IoT</p>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger"><i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    @foreach ($errors->all() as $error)
                        <div><i class="bi bi-exclamation-circle me-1"></i>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('visit.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label"><i class="bi bi-person-badge"></i> NIM / ID Mahasiswa</label>
                    <div class="input-group">
                        <input type="text" name="visitor_id" id="nimInput" class="form-control" 
                               placeholder="Contoh: J0404241017" value="{{ old('visitor_id') }}" required>
                        <button type="button" id="qrScanBtn" class="btn-scan" title="Scan QR Code">
                            <i class="bi bi-qr-code-scan fs-5"></i>
                        </button>
                    </div>
                </div>

                <div id="qrScannerContainer" class="d-none">
                    <div class="qr-header">
                        <span><i class="bi bi-camera me-2"></i>Scan QR Code</span>
                        <button type="button" id="qrCloseBtn" class="qr-close"><i class="bi bi-x"></i></button>
                    </div>
                    <div id="qrReader"></div>
                    <div id="qrError" class="alert alert-danger mt-2 mb-0 d-none"></div>
                </div>

                <div class="mb-4">
                    <label class="form-label"><i class="bi bi-clipboard-check"></i> Keperluan</label>
                    <select name="purpose" id="purposeSelect" class="form-select" required>
                        <option value="">-- Pilih Keperluan --</option>
                        <option value="belajar" {{ old('purpose') == 'belajar' ? 'selected' : '' }}>📚 Belajar Saja</option>
                        <option value="pinjam" {{ old('purpose') == 'pinjam' ? 'selected' : '' }}>📦 Peminjaman Barang</option>
                    </select>
                </div>

                <div id="borrowDetailBox" class="borrow-section d-none">
                    <div class="borrow-header"><i class="bi bi-box-seam"></i> Detail Peminjaman</div>
                    <div class="mb-3">
                        <label class="form-label">Pilih Barang</label>
                        <select name="item_id" id="itemSelect" class="form-select">
                            <option value="">-- Pilih Barang --</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" {{ old('item_id') == $item->id ? 'selected' : '' }}>
                                    {{ $item->name }} (Stok: {{ $item->current_stock }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">Jumlah</label>
                        <input type="number" name="quantity" id="quantityInput" class="form-control" min="1" value="{{ old('quantity', 1) }}">
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="bi bi-box-arrow-in-right"></i> Tap In Sekarang
                </button>

                <div class="tap-out-section">
                    <a href="{{ route('visit.tap-out') }}" class="tap-out-link">
                        <i class="bi bi-box-arrow-right"></i> Sudah selesai? Tap Out
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Time display
        function updateTime() {
            const now = new Date();
            document.getElementById('currentTime').textContent = now.toLocaleString('id-ID', {
                weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
                hour: '2-digit', minute: '2-digit'
            });
        }
        updateTime();
        setInterval(updateTime, 1000);

        const purposeSelect = document.getElementById('purposeSelect');
        const detailBox = document.getElementById('borrowDetailBox');
        const itemSelect = document.getElementById('itemSelect');
        const quantityInput = document.getElementById('quantityInput');
        const nimInput = document.getElementById('nimInput');
        const qrScanBtn = document.getElementById('qrScanBtn');
        const qrScannerContainer = document.getElementById('qrScannerContainer');
        const qrCloseBtn = document.getElementById('qrCloseBtn');
        const qrError = document.getElementById('qrError');
        let html5QrCode = null;
        let isScanning = false;

        function showQrError(msg) { qrError.textContent = msg; qrError.classList.remove('d-none'); }
        function hideQrError() { qrError.classList.add('d-none'); }

        function startQrScanner() {
            hideQrError();
            qrScannerContainer.classList.remove('d-none');
            if (!html5QrCode) html5QrCode = new Html5Qrcode("qrReader");
            html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                (decodedText) => { nimInput.value = decodedText; stopQrScanner(); nimInput.focus(); },
                () => {}
            ).then(() => { isScanning = true; }).catch((err) => {
                let msg = 'Gagal mengakses kamera.';
                if (err.name === 'NotAllowedError') msg = 'Akses kamera ditolak.';
                else if (err.name === 'NotFoundError') msg = 'Kamera tidak ditemukan.';
                showQrError(msg);
            });
        }

        function stopQrScanner() {
            if (html5QrCode && isScanning) {
                html5QrCode.stop().then(() => { isScanning = false; qrScannerContainer.classList.add('d-none'); hideQrError(); })
                .catch(() => { isScanning = false; qrScannerContainer.classList.add('d-none'); });
            } else { qrScannerContainer.classList.add('d-none'); }
        }

        qrScanBtn?.addEventListener('click', () => isScanning ? stopQrScanner() : startQrScanner());
        qrCloseBtn?.addEventListener('click', stopQrScanner);

        function toggleDetailBox() {
            if (purposeSelect.value === 'pinjam') {
                detailBox.classList.remove('d-none');
                itemSelect.required = true;
                quantityInput.required = true;
            } else {
                detailBox.classList.add('d-none');
                itemSelect.required = false;
                quantityInput.required = false;
            }
        }
        purposeSelect?.addEventListener('change', toggleDetailBox);
        toggleDetailBox();
    });
    </script>
</body>
</html>