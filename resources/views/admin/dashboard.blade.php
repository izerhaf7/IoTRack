@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('styles')
<style>
    .welcome-banner {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        border-radius: var(--radius-xl);
        padding: 2rem 2.5rem;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
    }

    .welcome-banner::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
        border-radius: 50%;
    }

    .welcome-banner::after {
        content: '';
        position: absolute;
        bottom: -30%;
        left: 10%;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
    }

    .welcome-content { position: relative; z-index: 1; }
    .welcome-content h2 { color: #fff; font-weight: 800; font-size: 1.75rem; margin-bottom: 0.5rem; text-shadow: 0 2px 4px rgba(0,0,0,0.2); }
    .welcome-content p { color: #fff; margin: 0; font-size: 0.95rem; text-shadow: 0 1px 2px rgba(0,0,0,0.2); }

    .welcome-stats {
        display: flex;
        gap: 2rem;
        margin-top: 1.5rem;
    }

    .welcome-stat {
        text-align: center;
        padding: 1rem 1.5rem;
        background: rgba(255,255,255,0.15);
        border-radius: var(--radius-md);
        backdrop-filter: blur(10px);
    }

    .welcome-stat-value { font-size: 2rem; font-weight: 800; color: #fff; line-height: 1; text-shadow: 0 2px 4px rgba(0,0,0,0.2); }
    .welcome-stat-label { font-size: 0.75rem; color: #fff; margin-top: 0.25rem; text-shadow: 0 1px 2px rgba(0,0,0,0.2); }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        background: var(--stat-color);
        opacity: 0.1;
        border-radius: 50%;
        transform: translate(30%, -30%);
    }

    .stat-card.primary { --stat-color: var(--primary); }
    .stat-card.success { --stat-color: var(--success); }
    .stat-card.warning { --stat-color: var(--warning); }
    .stat-card.info { --stat-color: var(--info); }

    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1rem;
    }

    .stat-card.primary .stat-icon { background: rgba(99, 102, 241, 0.15); color: var(--primary); }
    .stat-card.success .stat-icon { background: rgba(16, 185, 129, 0.15); color: var(--success); }
    .stat-card.warning .stat-icon { background: rgba(245, 158, 11, 0.15); color: var(--warning); }
    .stat-card.info .stat-icon { background: rgba(6, 182, 212, 0.15); color: var(--info); }

    .stat-value { font-size: 2rem; font-weight: 800; color: var(--text-primary); line-height: 1; }
    .stat-label { font-size: 0.8rem; color: var(--text-muted); margin-top: 0.5rem; font-weight: 500; }

    .stat-trend {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        font-size: 0.75rem;
        font-weight: 600;
        margin-top: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 2rem;
    }

    .stat-trend.up { background: rgba(16, 185, 129, 0.15); color: var(--success); }
    .stat-trend.down { background: rgba(239, 68, 68, 0.15); color: var(--danger); }

    .chart-card { height: 100%; }
    .chart-card .card-header { display: flex; align-items: center; justify-content: space-between; }
    .chart-card .card-title { font-weight: 700; font-size: 1rem; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: 0.5rem; }
    .chart-card .card-title i { color: var(--primary); }
    .chart-container { position: relative; height: 280px; }

    .quick-actions {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .quick-action {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        text-decoration: none;
        transition: var(--transition);
    }

    .quick-action:hover {
        border-color: var(--primary);
        transform: translateY(-4px);
        box-shadow: var(--shadow-lg);
    }

    .quick-action-icon {
        width: 50px;
        height: 50px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .quick-action.inventory .quick-action-icon { background: var(--gradient-primary); color: #fff; }
    .quick-action.borrowing .quick-action-icon { background: var(--gradient-warning); color: #fff; }
    .quick-action.visits .quick-action-icon { background: var(--gradient-success); color: #fff; }

    .quick-action-text h6 { font-weight: 700; color: var(--text-primary); margin: 0 0 0.25rem; font-size: 0.95rem; }
    .quick-action-text p { font-size: 0.75rem; color: var(--text-muted); margin: 0; }

    .activity-card .card-header { display: flex; align-items: center; justify-content: space-between; }
    .activity-card .card-title { font-weight: 700; font-size: 1rem; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: 0.5rem; }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px solid var(--border-light);
    }

    .activity-item:last-child { border-bottom: none; }

    .activity-avatar {
        width: 44px;
        height: 44px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        color: #fff;
        flex-shrink: 0;
    }

    .activity-avatar.belajar { background: var(--gradient-info); }
    .activity-avatar.pinjam { background: var(--gradient-warning); }

    .activity-info { flex: 1; min-width: 0; }
    .activity-name { font-weight: 600; color: var(--text-primary); font-size: 0.9rem; }
    .activity-detail { font-size: 0.75rem; color: var(--text-muted); }
    .activity-time { font-size: 0.75rem; color: var(--text-muted); font-weight: 500; white-space: nowrap; }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem;
    }

    .empty-state-icon {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: var(--bg-hover);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.75rem;
        color: var(--text-muted);
    }

    .empty-state h6 { color: var(--text-primary); font-weight: 600; margin-bottom: 0.25rem; }
    .empty-state p { color: var(--text-muted); font-size: 0.85rem; margin: 0; }

    @media (max-width: 1200px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 768px) {
        .stats-grid { grid-template-columns: 1fr; }
        .quick-actions { grid-template-columns: 1fr; }
        .welcome-stats { flex-direction: column; gap: 1rem; }
    }
</style>
@endsection

@section('content')
{{-- Welcome Banner --}}
<div class="welcome-banner">
    <div class="welcome-content">
        <h2>Selamat Datang Kembali! 👋</h2>
        <p>Berikut ringkasan aktivitas Lab IoT hari ini</p>
        <div class="welcome-stats">
            <div class="welcome-stat">
                <div class="welcome-stat-value">{{ $uniqueVisitorsCount ?? 0 }}</div>
                <div class="welcome-stat-label">Pengunjung</div>
            </div>
            <div class="welcome-stat">
                <div class="welcome-stat-value">{{ $activeBorrowings->count() }}</div>
                <div class="welcome-stat-label">Peminjaman Aktif</div>
            </div>
            <div class="welcome-stat">
                <div class="welcome-stat-value">{{ $todaysVisits->count() }}</div>
                <div class="welcome-stat-label">Total Kunjungan</div>
            </div>
        </div>
    </div>
</div>

{{-- Quick Actions --}}
<div class="quick-actions">
    <a href="{{ route('items.index') }}" class="quick-action inventory">
        <div class="quick-action-icon"><i class="bi bi-box-seam-fill"></i></div>
        <div class="quick-action-text">
            <h6>Inventaris</h6>
            <p>Kelola barang lab</p>
        </div>
    </a>
    <a href="{{ route('admin.borrowings.index') }}" class="quick-action borrowing">
        <div class="quick-action-icon"><i class="bi bi-arrow-left-right"></i></div>
        <div class="quick-action-text">
            <h6>Peminjaman</h6>
            <p>{{ $activeBorrowings->count() }} barang aktif</p>
        </div>
    </a>
    <a href="{{ route('admin.visits.index') }}" class="quick-action visits">
        <div class="quick-action-icon"><i class="bi bi-people-fill"></i></div>
        <div class="quick-action-text">
            <h6>Kunjungan</h6>
            <p>{{ $todaysVisits->count() }} hari ini</p>
        </div>
    </a>
</div>

{{-- Stats Grid --}}
<div class="stats-grid">
    <div class="card stat-card primary">
        <div class="stat-icon"><i class="bi bi-calendar-check-fill"></i></div>
        <div class="stat-value">{{ now()->format('d') }}</div>
        <div class="stat-label">{{ now()->isoFormat('MMMM Y') }}</div>
    </div>
    <div class="card stat-card success">
        <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
        <div class="stat-value">{{ $uniqueVisitorsCount ?? $todaysVisits->count() }}</div>
        <div class="stat-label">Pengunjung Hari Ini</div>
        <div class="stat-trend up"><i class="bi bi-arrow-up"></i> Aktif</div>
    </div>
    <div class="card stat-card warning">
        <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
        <div class="stat-value">{{ $activeBorrowings->count() }}</div>
        <div class="stat-label">Sedang Dipinjam</div>
    </div>
    <div class="card stat-card info">
        <div class="stat-icon"><i class="bi bi-graph-up-arrow"></i></div>
        <div class="stat-value">{{ $todaysVisits->count() }}</div>
        <div class="stat-label">Total Kunjungan</div>
    </div>
</div>

{{-- Activity Row --}}
<div class="row g-4">
    <div class="col-lg-6">
        <div class="card activity-card">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-exclamation-circle-fill text-warning"></i> Peminjaman Aktif</h6>
                <a href="{{ route('admin.borrowings.index', ['status' => 'dipinjam']) }}" class="btn btn-ghost btn-sm">Lihat Semua</a>
            </div>
            <div class="card-body">
                @forelse($activeBorrowings->take(5) as $borrow)
                <div class="activity-item">
                    <div class="activity-avatar pinjam">{{ strtoupper(substr($borrow->visit->visitor_name ?? 'U', 0, 1)) }}</div>
                    <div class="activity-info">
                        <div class="activity-name">{{ $borrow->visit->visitor_name ?? 'Unknown' }}</div>
                        <div class="activity-detail">{{ $borrow->item->name ?? 'Item' }} • {{ $borrow->quantity }} unit</div>
                    </div>
                    <div class="activity-time">{{ $borrow->created_at->diffForHumans() }}</div>
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#returnModal{{ $borrow->id }}">Terima</button>
                </div>
                {{-- Return Modal --}}
                <div class="modal fade" id="returnModal{{ $borrow->id }}" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-sm">
                        <div class="modal-content">
                            <div class="modal-body text-center p-4">
                                <div class="mb-3">
                                    <div style="width:64px;height:64px;background:rgba(99,102,241,0.15);border-radius:50%;display:inline-flex;align-items:center;justify-content:center">
                                        <i class="bi bi-check-lg fs-2" style="color:var(--primary)"></i>
                                    </div>
                                </div>
                                <h6 class="fw-bold mb-2">Konfirmasi Pengembalian</h6>
                                <p class="text-muted small mb-3">Terima {{ $borrow->quantity }} {{ $borrow->item->name ?? 'item' }} dari {{ $borrow->visit->visitor_name ?? 'Unknown' }}?</p>
                                <div class="d-flex gap-2 justify-content-center">
                                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Batal</button>
                                    <form action="{{ route('admin.return', $borrow->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary">Ya, Terima</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="bi bi-check-circle"></i></div>
                    <h6>Semua Dikembalikan</h6>
                    <p>Tidak ada peminjaman aktif</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card activity-card">
            <div class="card-header">
                <h6 class="card-title"><i class="bi bi-clock-history text-info"></i> Kunjungan Terbaru</h6>
                <a href="{{ route('admin.visits.index') }}" class="btn btn-ghost btn-sm">Lihat Semua</a>
            </div>
            <div class="card-body">
                @forelse($todaysVisits->take(5) as $visit)
                <div class="activity-item">
                    <div class="activity-avatar {{ $visit->purpose }}">{{ strtoupper(substr($visit->visitor_name, 0, 1)) }}</div>
                    <div class="activity-info">
                        <div class="activity-name">{{ $visit->visitor_name }}</div>
                        <div class="activity-detail">{{ $visit->visitor_id }} • {{ $visit->purpose === 'belajar' ? 'Belajar' : 'Meminjam' }}</div>
                    </div>
                    <div class="activity-time">{{ $visit->created_at->format('H:i') }}</div>
                </div>
                @empty
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="bi bi-calendar-x"></i></div>
                    <h6>Belum Ada Kunjungan</h6>
                    <p>Belum ada kunjungan hari ini</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const gridColor = isDark ? '#334155' : '#e2e8f0';
    
    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    Chart.defaults.color = textColor;
    



});
</script>
@endsection