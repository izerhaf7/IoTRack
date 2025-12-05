# IoTrack

Sistem pelacak kunjungan lab dan peminjaman peralatan untuk laboratorium IoT.

## Fitur

### Tap In / Tap Out
- **Tap In**: Check-in pengunjung dengan NIM, pilih tujuan (belajar/pinjam)
- **Tap Out**: Check-out pengunjung dengan validasi peminjaman aktif
- **QR Code Scanning**: Scan QR code berisi NIM untuk Tap In cepat tanpa mengetik manual
- **Validasi Tap Out**: Hanya pengunjung dengan peminjaman aktif yang dapat Tap Out
- **Single Tap Out**: Setiap kunjungan hanya dapat di-Tap Out sekali

### Peminjaman Barang
- Peminjaman barang dengan manajemen stok otomatis
- Pengembalian barang saat Tap Out
- Row-level locking untuk mencegah race condition pada update stok

### Dashboard Admin
- Sidebar navigasi untuk akses mudah ke berbagai menu
- Metric cards: jumlah peminjaman aktif dan pengunjung hari ini
- Grafik analitik:
  - **Barang Paling Sering Dipinjam**: Bar chart frekuensi peminjaman
  - **Pengunjung Harian**: Line chart jumlah pengunjung 7 hari terakhir
  - **Distribusi Tujuan**: Pie chart proporsi belajar vs pinjam
- Tabel data: peminjaman aktif dan kunjungan hari ini

### Export Data
- Export riwayat kunjungan harian ke file CSV
- Format: Tanggal, Waktu, Nama, NIM, Tujuan, Detail Peminjaman
- Nama file: `visits_YYYY-MM-DD.csv`

### Lokalisasi
- Seluruh antarmuka dalam Bahasa Indonesia
- Pesan error dan validasi dalam Bahasa Indonesia

## Teknologi

- **Backend**: Laravel 11
- **Database**: MySQL / SQLite
- **Frontend**: Bootstrap 5.3, Chart.js
- **QR Scanner**: html5-qrcode

## Instalasi

```bash
# Clone repository
git clone <repository-url>
cd iotrack

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate
php artisan db:seed

# Build assets
npm run build

# Jalankan server
php artisan serve
```

## Penggunaan

### Tap In
1. Akses halaman utama
2. Masukkan NIM atau scan QR code
3. Pilih tujuan (Belajar / Pinjam Barang)
4. Jika meminjam, pilih barang dan jumlah
5. Klik "Tap In"

### Tap Out
1. Akses halaman Tap Out
2. Masukkan NIM
3. Klik "Tap Out"
4. Sistem akan mengembalikan semua barang yang dipinjam

### Admin Dashboard
1. Login di `/admin/login`
2. Lihat statistik dan grafik di dashboard
3. Export data kunjungan dengan tombol "Export CSV"

## Struktur Kode

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── VisitController.php      # Tap In/Out
│   │   ├── AdminController.php      # Dashboard admin
│   │   └── Auth/
│   │       └── AdminAuthController.php  # Autentikasi admin
│   └── Requests/
│       ├── TapInRequest.php         # Validasi Tap In
│       └── TapOutRequest.php        # Validasi Tap Out
├── Services/
│   ├── VisitService.php             # Logika bisnis kunjungan
│   ├── BorrowingService.php         # Logika bisnis peminjaman
│   └── AnalyticsService.php         # Agregasi data analitik
├── Exports/
│   └── VisitsExport.php             # Export CSV kunjungan
└── Models/
    ├── Visit.php                    # Model kunjungan
    ├── Borrowing.php                # Model peminjaman
    ├── Item.php                     # Model barang
    └── Student.php                  # Model mahasiswa
```

## Testing

```bash
# Jalankan semua test
php artisan test

# Jalankan test dengan coverage
php artisan test --coverage
```

## Lisensi

MIT License
