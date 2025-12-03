<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Terima Kasih - Lab IoT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body text-center p-5">

                        {{-- Judul utama --}}
                        <h3 class="fw-bold mb-3 text-success">
                            <i class="bi bi-hand-thumbs-up-fill me-1"></i> Terima Kasih
                        </h3>

                        {{-- Nama pengunjung --}}
                        <h4 class="fw-bold mb-4 text-dark">
                            {{ $name }}
                        </h4>

                        {{-- Pesan utama --}}
                        <p class="mb-3">
                            Kunjungan Anda ke <strong>Lab IoT</strong> sudah tercatat dengan baik.
                        </p>

                        <p class="mb-4">
                            <strong class="text-danger">
                                Pastikan area kerja tetap rapi dan aman sebelum meninggalkan lab.
                            </strong>
                        </p>

                        {{-- Tombol navigasi --}}
                        <div class="d-flex justify-content-center gap-3 mt-3 flex-wrap">
                            <a href="{{ route('visit.create') }}" class="btn btn-outline-primary">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Tap In Lagi
                            </a>
                            <a href="{{ route('visit.tap-out') }}" class="btn btn-secondary">
                                <i class="bi bi-box-arrow-right me-1"></i> Tap Out Pengunjung Lain
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bootstrap JS (kalau mau pakai komponen yang butuh JS) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
