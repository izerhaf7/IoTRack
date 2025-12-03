<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tap In - Lab IoT</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #f8f9fa;
            /* GANTI height jadi min-height agar bisa discroll */
            min-height: 100vh; 
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card-custom {
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); /* Bayangan lebih halus */
            border: none; /* Hilangkan garis border bawaan */
            border-radius: 15px;
            overflow: hidden; /* Agar header tidak keluar radius */
        }
        .header-lab {
            background: linear-gradient(135deg, #0d6efd, #0a58ca); /* Gradasi Biru */
            color: white;
            padding: 25px;
            text-align: center;
        }
        /* Perbaikan tampilan di Mobile */
        @media (max-width: 576px) {
            .container { padding-left: 10px; padding-right: 10px; }
        }
    </style>
</head>
<body>

<!-- Tombol Login Admin Melayang -->
<div class="position-absolute top-0 end-0 p-3">
    <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-sm rounded-pill">
        <i class="bi bi-shield-lock"></i> Admin
    </a>
</div>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-5">

            <div class="card card-custom">
                <div class="header-lab">
                    <h3 class="fw-bold"><i class="bi bi-cpu"></i> IoTrack</h3>
                    <p class="mb-0 opacity-75">Sistem Pencatatan Lab IoT</p>
                </div>

                <div class="card-body p-4">

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

                    @if($errors->any())
                        <div class="alert alert-danger shadow-sm">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('visit.store') }}" method="POST">
                        @csrf 
                        
                        <div class="mb-3">
                            <label for="visitor_name" class="form-label fw-semibold text-secondary">Nama Lengkap</label>
                            <input type="text" class="form-control form-control-lg fs-6" id="visitor_name" name="visitor_name" value="{{ old('visitor_name') }}" required placeholder="Contoh: Budi Santoso">
                        </div>

                        <div class="mb-3">
                            <label for="visitor_id" class="form-label fw-semibold text-secondary">NIM / ID</label>
                            <input type="text" class="form-control form-control-lg fs-6" id="visitor_id" name="visitor_id" value="{{ old('visitor_id') }}" required placeholder="Contoh: J0404241017">
                        </div>

                        <div class="mb-3">
                            <label for="purpose" class="form-label fw-semibold text-secondary">Keperluan</label>
                            <select class="form-select form-select-lg fs-6" id="purpose" name="purpose" required>
                                <option value="" selected disabled>-- Pilih Keperluan --</option>
                                <option value="belajar" {{ old('purpose') == 'belajar' ? 'selected' : '' }}>Hanya Belajar / Berkunjung</option>
                                <option value="pinjam" {{ old('purpose') == 'pinjam' ? 'selected' : '' }}>Peminjaman Alat/Barang</option>
                            </select>
                        </div>

                        <div id="borrowing-section" class="d-none bg-light p-3 rounded-3 border mb-4">
                            <h6 class="text-primary fw-bold mb-3"><i class="bi bi-box-seam"></i> Detail Peminjaman</h6>
                            
                            <div class="mb-3">
                                <label for="item_id" class="form-label small text-muted">Pilih Barang</label>
                                <select class="form-select" id="item_id" name="item_id">
                                    <option value="" selected>-- Pilih Barang --</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}" {{ old('item_id') == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }} (Sisa Stok: {{ $item->current_stock }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="quantity" class="form-label small text-muted">Jumlah Pinjam</label>
                                <input type="number" class="form-control" id="quantity" name="quantity" min="1" placeholder="0" value="{{ old('quantity') }}">
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm">Tap In Masuk</button>
                        </div>

                        <div class="mt-4 text-center">
                            <a href="{{ route('visit.tap-out') }}" class="text-danger fw-bold text-decoration-none">
                                <i class="bi bi-box-arrow-right"></i> Klik disini untuk Tap Out
                            </a>
                        </div>

                    </form>
                </div> </div> </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const purposeSelect = document.getElementById('purpose');
        const borrowingSection = document.getElementById('borrowing-section');
        const inputItem = document.getElementById('item_id');
        const inputQty = document.getElementById('quantity');

        function toggleBorrowingSection() {
            if (!purposeSelect || !borrowingSection) return;

            if (purposeSelect.value === 'pinjam') {
                borrowingSection.classList.remove('d-none');
                if(inputItem) inputItem.setAttribute('required', 'required');
                if(inputQty) inputQty.setAttribute('required', 'required');
            } else {
                borrowingSection.classList.add('d-none');
                if(inputItem) inputItem.removeAttribute('required');
                if(inputQty) inputQty.removeAttribute('required');
                // Optional: Reset nilai jika disembunyikan
                // if(inputItem) inputItem.value = "";
                // if(inputQty) inputQty.value = "";
            }
        }

        if (purposeSelect) {
            purposeSelect.addEventListener('change', toggleBorrowingSection);
        }

        // Jalankan saat load agar status old input terjaga
        toggleBorrowingSection();
    });
</script>

</body>
</html>