@extends('admin.layouts.app')

@section('title', 'Kunjungan')
@section('page-title', 'Manajemen Kunjungan')

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

    .page-header h2 { font-size: 1.5rem; font-weight: 800; color: var(--text-primary); margin: 0; }
    .page-header p { color: var(--text-secondary); margin: 0.25rem 0 0; font-size: 0.9rem; }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
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

    .mini-stat.primary .mini-stat-icon { background: rgba(99, 102, 241, 0.15); color: var(--primary); }
    .mini-stat.success .mini-stat-icon { background: rgba(16, 185, 129, 0.15); color: var(--success); }
    .mini-stat.info .mini-stat-icon { background: rgba(6, 182, 212, 0.15); color: var(--info); }
    .mini-stat.warning .mini-stat-icon { background: rgba(245, 158, 11, 0.15); color: var(--warning); }

    .mini-stat-value { font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1; }
    .mini-stat-label { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem; font-weight: 500; }

    .filter-bar {
        display: flex;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1.5rem;
        padding: 1.25rem 1.5rem;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
    }

    .search-box {
        position: relative;
        flex: 1;
        min-width: 220px;
    }

    .search-box input { padding-left: 2.75rem; height: 44px; }
    .search-box i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); }

    .filter-group { display: flex; align-items: center; gap: 0.5rem; }
    .filter-group label { font-size: 0.8rem; color: var(--text-muted); font-weight: 500; white-space: nowrap; }

    .visitor-cell {
        display: flex;
        align-items: center;
        gap: 0.875rem;
    }

    .visitor-avatar {
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

    .visitor-avatar.belajar { background: var(--gradient-info); }
    .visitor-avatar.pinjam { background: var(--gradient-warning); }

    .visitor-name { font-weight: 600; color: var(--text-primary); font-size: 0.9rem; }
    .visitor-id { font-size: 0.75rem; color: var(--text-muted); font-family: monospace; }

    .purpose-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.4rem 0.875rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .purpose-pill.belajar { background: rgba(6, 182, 212, 0.15); color: var(--info); }
    .purpose-pill.pinjam { background: rgba(245, 158, 11, 0.15); color: var(--warning); }

    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.35rem 0.75rem;
        border-radius: 2rem;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .status-pill.active { background: rgba(16, 185, 129, 0.15); color: var(--success); }
    .status-pill.left { background: var(--bg-hover); color: var(--text-muted); }
    .status-pill.borrowing { background: rgba(245, 158, 11, 0.15); color: var(--warning); }

    .time-cell .time { font-weight: 600; color: var(--text-primary); font-size: 0.9rem; }
    .time-cell .date { font-size: 0.75rem; color: var(--text-muted); }

    .borrowed-items { display: flex; flex-wrap: wrap; gap: 0.375rem; }
    .borrowed-item {
        padding: 0.25rem 0.5rem;
        background: var(--bg-hover);
        border-radius: var(--radius-sm);
        font-size: 0.7rem;
        color: var(--text-secondary);
    }

    .action-btns { display: flex; gap: 0.5rem; justify-content: center; }

    .action-btn {
        width: 34px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--radius-md);
        border: none;
        cursor: pointer;
        transition: var(--transition);
    }

    .action-btn.view { background: rgba(99, 102, 241, 0.1); color: var(--primary); }
    .action-btn.view:hover { background: var(--primary); color: #fff; }
    .action-btn.delete { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
    .action-btn.delete:hover { background: var(--danger); color: #fff; }

    .empty-state { text-align: center; padding: 4rem 2rem; }
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
        <h2>Manajemen Kunjungan</h2>
        <p>Pantau dan kelola semua kunjungan ke Lab IoT</p>
    </div>
    <a href="{{ route('admin.visits.export') }}" class="btn btn-success">
        <i class="bi bi-download me-2"></i> Ekspor CSV
    </a>
</div>

<div class="stats-row">
    <div class="card mini-stat primary">
        <div class="mini-stat-icon"><i class="bi bi-people-fill"></i></div>
        <div>
            <div class="mini-stat-value">{{ $todayVisits }}</div>
            <div class="mini-stat-label">Total Kunjungan</div>
        </div>
    </div>
    <div class="card mini-stat info">
        <div class="mini-stat-icon"><i class="bi bi-book-fill"></i></div>
        <div>
            <div class="mini-stat-value">{{ $studyVisits }}</div>
            <div class="mini-stat-label">Belajar</div>
        </div>
    </div>
    <div class="card mini-stat warning">
        <div class="mini-stat-icon"><i class="bi bi-box-arrow-up-right"></i></div>
        <div>
            <div class="mini-stat-value">{{ $borrowVisits }}</div>
            <div class="mini-stat-label">Meminjam</div>
        </div>
    </div>
</div>

<div class="filter-bar">
    <div class="search-box">
        <i class="bi bi-search"></i>
        <input type="text" class="form-control" id="searchInput" placeholder="Cari nama atau NIM...">
    </div>
    <form method="GET" class="d-flex align-items-center gap-3 flex-wrap">
        <div class="filter-group">
            <label>Tanggal:</label>
            <input type="date" name="date" class="form-control form-control-sm" value="{{ $selectedDate }}" onchange="this.form.submit()">
        </div>
        <div class="filter-group">
            <label>Tujuan:</label>
            <select name="purpose" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua</option>
                <option value="belajar" {{ request('purpose') === 'belajar' ? 'selected' : '' }}>Belajar</option>
                <option value="pinjam" {{ request('purpose') === 'pinjam' ? 'selected' : '' }}>Meminjam</option>
            </select>
        </div>
        @if(request('date') || request('purpose'))
            <a href="{{ route('admin.visits.index') }}" class="btn btn-ghost btn-sm"><i class="bi bi-x-lg"></i> Reset</a>
        @endif
    </form>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold d-flex align-items-center gap-2">
            <i class="bi bi-journal-text" style="color: var(--primary)"></i> Riwayat Kunjungan
        </h6>
        <span class="badge badge-primary">{{ $visits->total() }} data</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table" id="visitsTable">
                <thead>
                    <tr>
                        <th>Pengunjung</th>
                        <th>Tujuan</th>
                        <th>Waktu Masuk</th>
                        <th>Waktu Keluar</th>
                        <th>Barang Dipinjam</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($visits as $visit)
                    <tr>
                        <td>
                            <div class="visitor-cell">
                                <div class="visitor-avatar {{ $visit->purpose }}">{{ strtoupper(substr($visit->visitor_name, 0, 1)) }}</div>
                                <div>
                                    <div class="visitor-name">{{ $visit->visitor_name }}</div>
                                    <div class="visitor-id">{{ $visit->visitor_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($visit->purpose === 'belajar')
                                <span class="purpose-pill belajar"><i class="bi bi-book"></i> Belajar</span>
                            @else
                                <span class="purpose-pill pinjam"><i class="bi bi-box"></i> Meminjam</span>
                            @endif
                        </td>
                        <td>
                            <div class="time-cell">
                                <div class="time">{{ $visit->created_at->format('H:i') }}</div>
                                <div class="date">{{ $visit->created_at->format('d M Y') }}</div>
                            </div>
                        </td>
                        <td>
                            @if($visit->tapped_out_at)
                                <div class="time-cell">
                                    <div class="time">{{ $visit->tapped_out_at->format('H:i') }}</div>
                                    <div class="date">{{ $visit->created_at->diffForHumans($visit->tapped_out_at, true) }}</div>
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($visit->borrowings->count() > 0)
                                <div class="borrowed-items">
                                    @foreach($visit->borrowings->take(2) as $borrow)
                                        <span class="borrowed-item">{{ $borrow->item->name ?? 'Item' }} ({{ $borrow->quantity }})</span>
                                    @endforeach
                                    @if($visit->borrowings->count() > 2)
                                        <span class="borrowed-item">+{{ $visit->borrowings->count() - 2 }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @php $hasActive = $visit->borrowings->contains('status', 'dipinjam'); @endphp
                            @if($hasActive)
                                <span class="status-pill borrowing"><i class="bi bi-clock"></i> Meminjam</span>
                            @elseif($visit->tapped_out_at)
                                <span class="status-pill left"><i class="bi bi-box-arrow-right"></i> Selesai</span>
                            @else
                                <span class="status-pill active"><i class="bi bi-person-check"></i> Di Lab</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-btns">
                                <button class="action-btn view" data-bs-toggle="modal" data-bs-target="#detailModal{{ $visit->id }}" title="Detail"><i class="bi bi-eye"></i></button>
                                @if(!$hasActive)
                                <button class="action-btn delete" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $visit->id }}" title="Hapus"><i class="bi bi-trash"></i></button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @include('admin.visits._modals', ['visit' => $visit])
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="empty-state-icon"><i class="bi bi-calendar-x"></i></div>
                                <h5>Tidak Ada Kunjungan</h5>
                                <p>Belum ada data kunjungan untuk tanggal yang dipilih</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($visits->hasPages())
    <div class="card-footer d-flex justify-content-center">{{ $visits->appends(request()->query())->links() }}</div>
    @endif
</div>
@endsection

@section('scripts')
<script>
document.getElementById('searchInput').addEventListener('input', function() {
    const term = this.value.toLowerCase();
    document.querySelectorAll('#visitsTable tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
    });
});
</script>
@endsection