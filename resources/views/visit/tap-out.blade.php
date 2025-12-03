<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tap Out - Lab IoT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #f8f9fa;
            /* Agar bisa discroll jika konten panjang */
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-custom {
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }
        .header-out {
            /* Gradasi Merah untuk menandakan Keluar/Stop */
            background: linear-gradient(135deg, #dc3545, #b02a37);
            color: white;
            padding: 25px;
            text-align: center;
        }
        /* Responsif untuk HP */
        @media (max-width: 576px) {
            .container { padding-left: 10px; padding-right: 10px; }
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-4">
            
            <div class="card card-custom">
                <div class="header-out">
                    <h3 class="fw-bold"><i class="bi bi-door-open-fill"></i> Tap Out</h3>
                    <p class="mb-0 opacity-75">Selesai Kunjungan</p>
                </div>

                <div class="card-body p-4">
                    
                    <div class="text-center mb-4">
                        <p class="text-muted small">Masukkan NIM Anda untuk mengembalikan barang dan menyelesaikan sesi.</p>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('visit.tap-out-process') }}" method="POST">
                        @csrf <div class="mb-4">
                            <label class="form-label fw-semibold text-secondary">NIM / ID Pengunjung</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-person-badge"></i></span>
                                <input type="text" name="visitor_id" class="form-control form-control-lg fs-6" required placeholder="Contoh: J0404241017">
                            </div>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger btn-lg shadow-sm fw-bold">
                                <i class="bi bi-box-arrow-right"></i> Tap Out Sekarang
                            </button>
                            <a href="{{ route('visit.create') }}" class="btn btn-outline-secondary btn-lg">
                                <i class="bi bi-arrow-left"></i> Kembali ke Tap In
                            </a>
                        </div>
                    </form>

                </div> </div> </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>