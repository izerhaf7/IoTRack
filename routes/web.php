<?php

use Illuminate\Support\Facades\Route;

// Controller
use App\Http\Controllers\VisitController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\Auth\AdminAuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

/*
|==========================================
| BAGIAN 1: PENGUNJUNG / UMUM (PUBLIC)
|==========================================
| Route ini bisa diakses siapa saja tanpa login
*/

// Halaman Utama (Form Tap In)
Route::get('/', [VisitController::class, 'create'])->name('visit.create');

// Proses Simpan Data Tap In
Route::post('/tap-in', [VisitController::class, 'store'])->name('visit.store');

// Halaman "Selamat Datang" setelah Tap In
Route::get('/tap-in/welcome/{visit}', [VisitController::class, 'welcome'])
    ->name('visit.welcome');

// Halaman Form Tap Out
Route::get('/tap-out', [VisitController::class, 'tapOutForm'])->name('visit.tap-out');

// Proses Tap Out
Route::post('/tap-out-process', [VisitController::class, 'tapOutProcess'])
    ->name('visit.tap-out-process');

// Halaman "Terima kasih" setelah Tap Out
Route::get('/tap-out/thankyou/{visitor_id}', [VisitController::class, 'goodbye'])
    ->name('visit.goodbye');


/*
|==========================================
| BAGIAN 2: OTENTIKASI ADMIN (LOGIN & LOGOUT)
|==========================================
| Route otentikasi admin dengan prefix /admin sesuai Laravel conventions
*/

// Menampilkan Form Login Admin
Route::get('/admin/login', [AdminAuthController::class, 'showLoginForm'])->name('login');

// Proses Login Admin
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login.process');

// Proses Logout Admin
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('logout');


/*
|==========================================
| BAGIAN 3: ADMIN AREA (PROTECTED)
|==========================================
| Semua route di dalam grup ini HANYA bisa diakses jika sudah Login.
| Menggunakan prefix /admin dan middleware auth.
*/

Route::middleware(['auth'])->prefix('admin')->group(function () {

    // 1. Menu Utama Admin (Landing Page setelah Login)
    Route::get('/menu', function () {
        return view('admin.menu');
    })->name('admin.menu');

    // 2. Dashboard Pantau Pengunjung
    Route::get('/dashboard', [AdminController::class, 'index'])
        ->name('admin.dashboard');

    // 3. Manajemen Peminjaman
    Route::get('/borrowings', [AdminController::class, 'borrowingsIndex'])
        ->name('admin.borrowings.index');

    // 4. Proses Pengembalian Barang (Tombol "Terima Barang")
    Route::post('/return/{id}', [AdminController::class, 'returnItem'])
        ->name('admin.return');

    // 5. Manajemen Barang (CRUD Tambah, Edit, Hapus Barang)
    Route::resource('items', ItemController::class);

    // 6. Ekspor data kunjungan ke CSV (harus sebelum /visits)
    Route::get('/visits/export', [AdminController::class, 'exportVisits'])
        ->name('admin.visits.export');

    // 7. Manajemen Kunjungan
    Route::get('/visits', [AdminController::class, 'visitsIndex'])
        ->name('admin.visits.index');

    // 8. Hapus riwayat kunjungan secara manual
    Route::delete('/visit/delete/{id}', [AdminController::class, 'destroyVisit'])
        ->name('admin.visit.destroy');
});
