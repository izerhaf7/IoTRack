<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Selamat Datang - Lab IoT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="min-height: 100vh;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body text-center p-5">

                        <h3 class="fw-bold mb-3 text-primary">Selamat Datang</h3>
                        <h4 class="fw-bold mb-4 text-dark">{{ $visit->visitor_name }}</h4>

                        <p class="mb-4">
                            Silakan berkunjung dan jangan lupa
                            <strong style="color: #dc3545;">patuhi aturan lab IoT</strong>.
                        </p>

                        <div class="d-flex justify-content-center gap-3 mt-3">
                            <a href="{{ route('visit.create') }}" class="btn btn-outline-primary">
                                Kembali ke Tap In
                            </a>
                            <a href="{{ route('visit.tap-out') }}" class="btn btn-secondary">
                                Tap Out
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
