@extends('admin.layouts.app')

@section('title', 'Menu Admin')
@section('page-title', 'Menu Utama')

@section('styles')
<style>
    .hero-section {
        text-align: center;
        padding: 1rem 1rem 2.5rem;
        margin-bottom: 2.5rem;
        position: relative;
    }

    .hero-icon {
        width: 90px;
        height: 90px;
        background: var(--gradient-primary);
        border-radius: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 2.25rem;
        color: #fff;
        box-shadow: 0 15px 35px rgba(99, 102, 241, 0.3);
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
    }

    .hero-title {
        font-size: 2.25rem;
        font-weight: 800;
        color: var(--text-primary);
        margin-bottom: 0.75rem;
        text-align: center;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    [data-theme="dark"] .hero-title {
        color: #fff;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    }

    .hero-subtitle {
        font-size: 1.05rem;
        color: var(--text-secondary);
        max-width: 500px;
        margin: 0 auto;
        text-align: center;
        line-height: 1.6;
    }

    [data-theme="dark"] .hero-subtitle {
        color: #94a3b8;
    }

    .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
        max-width: 1100px;
        margin: 0 auto;
    }

    .menu-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        padding: 2rem;
        text-decoration: none;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .menu-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--card-gradient);
        opacity: 0;
        transition: var(--transition);
    }

    .menu-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-xl);
        border-color: transparent;
    }

    .menu-card:hover::before { opacity: 1; }

    .menu-card.dashboard { --card-gradient: var(--gradient-success); --card-color: var(--success); }
    .menu-card.inventory { --card-gradient: var(--gradient-primary); --card-color: var(--primary); }
    .menu-card.borrowing { --card-gradient: var(--gradient-warning); --card-color: var(--warning); }
    .menu-card.visits { --card-gradient: var(--gradient-info); --card-color: var(--info); }
    .menu-card.export { --card-gradient: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); --card-color: #8b5cf6; }

    .menu-icon {
        width: 70px;
        height: 70px;
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
        color: #fff;
        margin-bottom: 1.5rem;
        transition: var(--transition);
    }

    .menu-card.dashboard .menu-icon { background: var(--gradient-success); }
    .menu-card.inventory .menu-icon { background: var(--gradient-primary); }
    .menu-card.borrowing .menu-icon { background: var(--gradient-warning); }
    .menu-card.visits .menu-icon { background: var(--gradient-info); }
    .menu-card.export .menu-icon { background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); }

    .menu-card:hover .menu-icon { transform: scale(1.1) rotate(-5deg); }

    .menu-title {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.5rem;
    }

    .menu-desc {
        font-size: 0.9rem;
        color: var(--text-secondary);
        line-height: 1.6;
        margin-bottom: 1.5rem;
        flex: 1;
    }

    .menu-action {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.875rem;
        transition: var(--transition);
        background: rgba(99, 102, 241, 0.1);
        color: var(--card-color);
    }

    .menu-card:hover .menu-action {
        background: var(--card-gradient);
        color: #fff;
    }

    @media (max-width: 768px) {
        .hero-title { font-size: 1.75rem; }
        .hero-icon { width: 80px; height: 80px; font-size: 2rem; }
        .menu-grid { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
<div class="hero-section">
    <div class="hero-icon">
        <i class="bi bi-person-check-fill"></i>
    </div>
    <h1 class="hero-title">Selamat Datang, Admin! 👋</h1>
    <p class="hero-subtitle">Kelola sistem Lab IoT dengan mudah. Pilih menu di bawah untuk memulai.</p>
</div>

<div class="menu-grid">
    <a href="{{ route('admin.dashboard') }}" class="menu-card dashboard">
        <div class="menu-icon">
            <i class="bi bi-speedometer2"></i>
        </div>
        <h3 class="menu-title">Dashboard</h3>
        <p class="menu-desc">Pantau aktivitas kunjungan, peminjaman, dan statistik lab secara real-time dengan visualisasi data yang menarik.</p>
        <span class="menu-action">
            <i class="bi bi-arrow-right"></i> Buka Dashboard
        </span>
    </a>

    <a href="{{ route('items.index') }}" class="menu-card inventory">
        <div class="menu-icon">
            <i class="bi bi-box-seam-fill"></i>
        </div>
        <h3 class="menu-title">Inventaris</h3>
        <p class="menu-desc">Kelola daftar barang, tambah item baru, dan perbarui stok peralatan lab dengan mudah.</p>
        <span class="menu-action">
            <i class="bi bi-arrow-right"></i> Kelola Barang
        </span>
    </a>

    <a href="{{ route('admin.borrowings.index') }}" class="menu-card borrowing">
        <div class="menu-icon">
            <i class="bi bi-arrow-left-right"></i>
        </div>
        <h3 class="menu-title">Peminjaman</h3>
        <p class="menu-desc">Lihat dan proses pengembalian barang yang sedang dipinjam oleh pengunjung lab.</p>
        <span class="menu-action">
            <i class="bi bi-arrow-right"></i> Lihat Peminjaman
        </span>
    </a>

    <a href="{{ route('admin.visits.index') }}" class="menu-card visits">
        <div class="menu-icon">
            <i class="bi bi-people-fill"></i>
        </div>
        <h3 class="menu-title">Kunjungan</h3>
        <p class="menu-desc">Pantau dan kelola semua riwayat kunjungan ke Lab IoT dengan filter dan pencarian.</p>
        <span class="menu-action">
            <i class="bi bi-arrow-right"></i> Lihat Kunjungan
        </span>
    </a>

    <a href="{{ route('admin.visits.export') }}" class="menu-card export">
        <div class="menu-icon">
            <i class="bi bi-file-earmark-arrow-down-fill"></i>
        </div>
        <h3 class="menu-title">Ekspor Laporan</h3>
        <p class="menu-desc">Unduh data kunjungan dalam format CSV untuk dokumentasi dan pelaporan.</p>
        <span class="menu-action">
            <i class="bi bi-download"></i> Unduh CSV
        </span>
    </a>
</div>
@endsection