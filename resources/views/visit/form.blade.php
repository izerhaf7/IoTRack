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
        .header-lab {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            color: white;
            padding: 25px;
            text-align: center;
        }
        @media (max-width: 576px) {
            .container { padding-left: 10px; padding-right: 10px; }
        }

        /* Animasi buka/tutup Detail Peminjaman */
        #borrowDetailBox {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            transition: max-height 0.4s ease, opacity 0.3s ease;
        }

        #borrowDetailBox.active {
            max-height: 500px;   /* cukup besar supaya muat isi */
            opacity: 1;
        }
    </style>
</head>
<body>

{{-- Tombol Login Admin --}}
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

                    {{-- Flash message --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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

                        {{-- NIM / ID --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">NIM / ID</label>
                            <input type="text"
                                   name="visitor_id"
                                   class="form-control"
                                   placeholder="Contoh: J0404241017"
                                   value="{{ old('visitor_id') }}"
                                   required>
                        </div>

                        {{-- Keperluan --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Keperluan</label>
                            <select name="purpose"
                                    id="purposeSelect"
                                    class="form-select"
                                    required>
                                <option value="">-- Pilih Keperluan --</option>
                                <option value="belajar" {{ old('purpose') == 'belajar' ? 'selected' : '' }}>
                                    Belajar Saja
                                </option>
                                <option value="pinjam" {{ old('purpose') == 'pinjam' ? 'selected' : '' }}>
                                    Peminjaman Alat/Barang
                                </option>
                            </select>
                        </div>

                        {{-- DETAIL PEMINJAMAN --}}
                        <div id="borrowDetailBox"
                             class="border rounded-3 p-3 bg-light mb-3 d-none">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-box-seam text-primary me-2"></i>
                                <span class="fw-semibold text-primary">Detail Peminjaman</span>
                            </div>

                            {{-- Pilih Barang --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Pilih Barang</label>
                                <select name="item_id"
                                        id="itemSelect"
                                        class="form-select">
                                    <option value="">-- Pilih Barang --</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}"
                                                {{ old('item_id') == $item->id ? 'selected' : '' }}>
                                            {{ $item->name }} (Sisa Stok: {{ $item->current_stock }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Jumlah Pinjam --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Jumlah Pinjam</label>
                                <input type="number"
                                       name="quantity"
                                       id="quantityInput"
                                       class="form-control"
                                       min="1"
                                       value="{{ old('quantity', 1) }}">
                            </div>
                        </div>

                        {{-- Tombol Submit --}}
                        <button type="submit" class="btn btn-primary w-100 mt-2">
                            Tap In Masuk
                        </button>

                        <div class="text-center mt-3">
                            <a href="{{ route('visit.tap-out') }}" class="text-danger fw-semibold text-decoration-none">
                                <i class="bi bi-box-arrow-right me-1"></i> Klik disini untuk Tap Out
                            </a>
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const purposeSelect  = document.getElementById('purposeSelect');
    const detailBox      = document.getElementById('borrowDetailBox');
    const itemSelect     = document.getElementById('itemSelect');
    const quantityInput  = document.getElementById('quantityInput');

    function showDetailBox() {
        // tampilkan dulu (hilangkan d-none)
        detailBox.classList.remove('d-none');

        // paksa reflow supaya transisi jalan
        void detailBox.offsetHeight;

        // aktifkan animasi slide
        detailBox.classList.add('active');

        itemSelect.required    = true;
        quantityInput.required = true;

        // scroll pelan ke detail box
        setTimeout(() => {
            const y = detailBox.getBoundingClientRect().top + window.scrollY - 80;
            window.scrollTo({
                top: y,
                behavior: 'smooth'
            });
        }, 250);
    }

    function hideDetailBox() {
        detailBox.classList.remove('active');
        itemSelect.required    = false;
        quantityInput.required = false;

        // setelah animasi selesai, baru disembunyikan total
        setTimeout(() => {
            detailBox.classList.add('d-none');
        }, 400);
    }

    function toggleDetailBox() {
        if (!purposeSelect) return;

        if (purposeSelect.value === 'pinjam') {
            showDetailBox();
        } else {
            hideDetailBox();
        }
    }

    if (purposeSelect) {
        purposeSelect.addEventListener('change', toggleDetailBox);
    }

    // Inisialisasi saat pertama load (untuk old() ketika validasi gagal)
    toggleDetailBox();
});
</script>

</body>
</html>
