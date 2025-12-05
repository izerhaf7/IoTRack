@extends('admin.layouts.app')

@section('title', 'Peminjaman')
@section('page-title', 'Manajemen Peminjaman')

@section('styles')
<style>
    .page-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .page-header h2 {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--text-primary);
        margin: 0;
    }

    .page-header p {
        color: var(--text-secondary);
        margin: 0.25rem 0 0;
        font-size: 0.9rem;
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .mini-stat {
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .mini-stat-icon {
        width: 52px;
        height: 52px;
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
    }

    .mini-stat.warning .mini-stat-icon { background: rgba(245, 158, 11, 0.15); color: var(--warning); }
    .mini-stat.success .mini-stat-icon { background: rgba(16, 185, 129, 0.15); color: var(--success); }
    .mini-stat.info .mini-stat-icon { background: rgba(6, 182, 212, 0.15); color: var(--info); }
    .mini-stat.danger .mini-stat-icon { background: rgba(239, 68, 68, 0.15); color: var(--danger); }

    .mini-stat-value { font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1; }
    .mini-stat-label { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem; font-weight: 500; }

    .filter-tabs {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        flex-wrap: wrap;
    }

    .filter-tab {
        padding: 0.625rem 1.25rem;
        border-radius: 2rem;
        font-size: 0.85rem;
        font-weight: 600;
        text-decoration: none;
        transition: var(--transition);
        border: 1px solid var(--border);
        background: var(--bg-card);
        color: var(--text-secondary);
    }

    .filter-tab:hover {
        border-color: var(--primary);
        color: var(--primary);
    }

    .filter-tab.active {
        background: var(--gradient-primary);
        border-color: transparent;
        color: #fff;
    }

    .filter-tab .count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        height: 22px;
        padding: 0 0.5rem;
        background: rgba(255,255,255,0.2);
        border-radius: 2rem;
        font-size: 0.7rem;
        margin-left: 0.5rem;
    }

    .filter-tab:not(.active) .count { background: var(--bg-hover); }

    .search-filter {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .search-box {
        position: relative;
        flex: 1;
        min-width: 250px;
        max-width: 350px;
    }

    .search-box input {
        padding-left: 2.75rem;
        height: 46px;
    }

    .search-box i {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-muted);
    }

    .borrower-cell {
        display: flex;
        align-items: center;
        gap: 0.875rem;
    }

    .borrower-avatar {
        width: 44px;
        height: 44px;
        border-radius: var(--radius-md);
        background: var(--gradient-primary);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    .borrower-name { font-weight: 600; color: var(--text-primary); font-size: 0.9rem; }
    .borrower-id { font-size: 0.75rem; color: var(--text-muted); font-family: monospace; }

    .item-cell {
        display: flex;
        align-items: center;
        gap: 0.875rem;
    }

    .item-image {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-md);
        object-fit: cover;
        background: var(--bg-hover);
        flex-shrink: 0;
    }

    .item-name { font-weight: 600; color: var(--text-primary); font-size: 0.9rem; }
    .item-qty { font-size: 0.75rem; color: var(--text-muted); }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.4rem 0.875rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .status-pill.active { background: rgba(245, 158, 11, 0.15); color: var(--warning); }
    .status-pill.returned { background: rgba(16, 185, 129, 0.15); color: var(--success); }
    .status-pill.overdue { background: rgba(239, 68, 68, 0.15); color: var(--danger); }

    .time-cell .time { font-weight: 600; color: var(--text-primary); font-size: 0.9rem; }
    .time-cell .date { font-size: 0.75rem; color: var(--text-muted); }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
    }

    .empty-state-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: var(--bg-hover);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 2rem;
        color: var(--text-muted);
    }

    .empty-state h5 { color: var(--text-primary); font-weight: 700; margin-bottom: 0.5rem; }
    .empty-state p { color: var(--text-muted); font-size: 0.9rem; margin: 0; }

    @media (max-width: 1200px) { .stats-row { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 768px) { .stats-row { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Manajemen Peminjaman</h2>
        <p>Kelola dan pantau semua peminjaman barang lab</p>
    </div>
</div>

<div class="stats-row">
    <div class="card mini-stat warning">
        <div class="mini-stat-icon"><i class="bi bi-hourglass-split"></i></div>
        <div>
            <div class="mini-stat-value">{{ $activeBorrowings->count() }}</div>
            <div class="mini-stat-label">Sedang Dipinjam</div>
        </div>
    </div>
    <div class="card mini-stat success">
        <div class="mini-stat-icon"><i class="bi bi-check-circle-fill"></i></div>
        <div>
            <div class="mini-stat-value">{{ $returnedToday }}</div>
            <div class="mini-stat-label">Dikembalikan Hari Ini</div>
        </div>
    </div>
    <div class="card mini-stat info">
        <div class="mini-stat-icon"><i class="bi bi-box-seam-fill"></i></div>
        <div>
            <div class="mini-stat-value">{{ $totalBorrowedItems }}</div>
            <div class="mini-stat-label">Total Item Dipinjam</div>
        </div>
    </div>
    <div class="card mini-stat danger">
        <div class="mini-stat-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <div>
            <div class="mini-stat-value">{{ $overdueBorrowings }}</div>
            <div class="mini-stat-label">Terlambat (>24 jam)</div>
        </div>
    </div>
</div>

<div class="filter-tabs">
    <a href="{{ route('admin.borrowings.index') }}" class="filter-tab {{ !request('status') ? 'active' : '' }}">
        Semua <span class="count">{{ $allCount }}</span>
    </a>
    <a href="{{ route('admin.borrowings.index', ['status' => 'dipinjam']) }}" class="filter-tab {{ request('status') === 'dipinjam' ? 'active' : '' }}">
        Aktif <span class="count">{{ $activeBorrowings->count() }}</span>
    </a>
    <a href="{{ route('admin.borrowings.index', ['status' => 'dikembalikan']) }}" class="filter-tab {{ request('status') === 'dikembalikan' ? 'active' : '' }}">
        Dikembalikan <span class="count">{{ $returnedCount }}</span>
    </a>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
            <i class="bi bi-list-ul" style="color: var(--primary)"></i> Daftar Peminjaman
        </h6>
        <div class="search-filter">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" id="searchInput" placeholder="Cari peminjam atau barang...">
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table" id="borrowingsTable">
                <thead>
                    <tr>
                        <th>Peminjam</th>
                        <th>Barang</th>
                        <th>Waktu Pinjam</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($borrowings as $borrow)
                    <tr>
                        <td>
                            <div class="borrower-cell">
                                <div class="borrower-avatar">{{ strtoupper(substr($borrow->visit->visitor_name ?? 'U', 0, 1)) }}</div>
                                <div>
                                    <div class="borrower-name">{{ $borrow->visit->visitor_name ?? 'Unknown' }}</div>
                                    <div class="borrower-id">{{ $borrow->visit->visitor_id ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="item-cell">
                                @if($borrow->item && $borrow->item->image)
                                    <img src="{{ asset('storage/' . $borrow->item->image) }}" alt="" class="item-image">
                                @else
                                    <div class="item-image d-flex align-items-center justify-content-center"><i class="bi bi-box text-muted"></i></div>
                                @endif
                                <div>
                                    <div class="item-name">{{ $borrow->item->name ?? 'Item dihapus' }}</div>
                                    <div class="item-qty">{{ $borrow->quantity }} unit</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="time-cell">
                                <div class="time">{{ $borrow->created_at->format('H:i') }}</div>
                                <div class="date">{{ $borrow->created_at->format('d M Y') }}</div>
                            </div>
                        </td>
                        <td>
                            @if($borrow->status === 'dipinjam')
                                @php $isOverdue = $borrow->created_at->diffInHours(now()) > 24; @endphp
                                @if($isOverdue)
                                    <span class="status-pill overdue"><i class="bi bi-exclamation-circle"></i> Terlambat</span>
                                @else
                                    <span class="status-pill active"><i class="bi bi-clock"></i> Dipinjam</span>
                                @endif
                            @else
                                <span class="status-pill returned"><i class="bi bi-check-circle"></i> Dikembalikan</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($borrow->status === 'dipinjam')
                                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#returnModal{{ $borrow->id }}">
                                    <i class="bi bi-check2-circle me-1"></i> Terima
                                </button>
                            @else
                                <span class="text-muted small">{{ $borrow->returned_at ? $borrow->returned_at->format('H:i, d M') : '-' }}</span>
                            @endif
                        </td>
                    </tr>
                    @if($borrow->status === 'dipinjam')
                    <div class="modal fade" id="returnModal{{ $borrow->id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-body text-center p-4">
                                    <div class="mb-3">
                                        <div style="width:72px;height:72px;background:rgba(99,102,241,0.15);border-radius:50%;display:inline-flex;align-items:center;justify-content:center">
                                            <i class="bi bi-check-lg fs-1" style="color:var(--primary)"></i>
                                        </div>
                                    </div>
                                    <h5 class="fw-bold mb-2">Konfirmasi Pengembalian</h5>
                                    <p class="text-muted mb-4">Terima pengembalian <strong>{{ $borrow->quantity }} {{ $borrow->item->name ?? 'item' }}</strong> dari <strong>{{ $borrow->visit->visitor_name ?? 'Unknown' }}</strong>?</p>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button type="button" class="btn btn-ghost px-4" data-bs-dismiss="modal">Batal</button>
                                        <form action="{{ route('admin.return', $borrow->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check2 me-1"></i> Ya, Terima</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @empty
                    <tr>
                        <td colspan="5">
                            <div class="empty-state">
                                <div class="empty-state-icon"><i class="bi bi-inbox"></i></div>
                                <h5>Tidak Ada Data</h5>
                                <p>Belum ada data peminjaman yang tersedia</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($borrowings->hasPages())
    <div class="card-footer d-flex justify-content-center">{{ $borrowings->links() }}</div>
    @endif
</div>
@endsection

@section('scripts')
<script>
document.getElementById('searchInput').addEventListener('input', function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll('#borrowingsTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
    });
});
</script>
@endsection