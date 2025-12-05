@extends('admin.layouts.app')

@section('title', 'Inventaris')
@section('page-title', 'Inventaris')

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
    .mini-stat.danger .mini-stat-icon { background: rgba(239, 68, 68, 0.15); color: var(--danger); }

    .mini-stat-value { font-size: 1.75rem; font-weight: 800; color: var(--text-primary); line-height: 1; }
    .mini-stat-label { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem; font-weight: 500; }

    .items-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    .item-card {
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        overflow: hidden;
        transition: var(--transition);
    }

    .item-card:hover {
        transform: translateY(-6px);
        box-shadow: var(--shadow-xl);
        border-color: var(--primary);
    }

    .item-image-wrapper {
        position: relative;
        height: 200px;
        background: var(--bg-hover);
        overflow: hidden;
    }

    .item-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: var(--transition);
    }

    .item-card:hover .item-image { transform: scale(1.08); }

    .item-stock-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        padding: 0.4rem 0.875rem;
        border-radius: 2rem;
        font-size: 0.75rem;
        font-weight: 700;
        backdrop-filter: blur(10px);
    }

    .item-stock-badge.available { background: rgba(16, 185, 129, 0.9); color: #fff; }
    .item-stock-badge.low { background: rgba(245, 158, 11, 0.9); color: #fff; }
    .item-stock-badge.empty { background: rgba(239, 68, 68, 0.9); color: #fff; }

    .item-body { padding: 1.5rem; }

    .item-name {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0 0 0.5rem;
    }

    .item-desc {
        font-size: 0.85rem;
        color: var(--text-secondary);
        line-height: 1.6;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        margin-bottom: 1.25rem;
        min-height: 2.7rem;
    }

    .item-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.75rem;
        padding: 1rem 0;
        border-top: 1px solid var(--border);
        border-bottom: 1px solid var(--border);
        margin-bottom: 1.25rem;
    }

    .item-stat { text-align: center; }
    .item-stat-value { font-size: 1.25rem; font-weight: 800; color: var(--text-primary); }
    .item-stat-label { font-size: 0.65rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; }

    .item-actions { display: flex; gap: 0.75rem; }
    .item-actions .btn { flex: 1; padding: 0.75rem; font-size: 0.85rem; }

    .empty-state {
        text-align: center;
        padding: 5rem 2rem;
        background: var(--bg-card);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
    }

    .empty-state-icon {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: var(--bg-hover);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 2.5rem;
        color: var(--text-muted);
    }

    .empty-state h4 { color: var(--text-primary); font-weight: 700; margin-bottom: 0.5rem; }
    .empty-state p { color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1.5rem; }

    .modal-header-gradient {
        background: var(--gradient-primary);
        color: #fff;
        border: none;
    }

    .modal-header-gradient .btn-close { filter: brightness(0) invert(1); }

    @media (max-width: 992px) { .stats-row { grid-template-columns: 1fr; } }
    @media (max-width: 576px) { .items-grid { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')
<div class="page-header">
    <div>
        <h2>Manajemen Inventaris</h2>
        <p>Kelola semua barang dan peralatan Lab IoT</p>
    </div>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-lg me-2"></i> Tambah Barang
    </button>
</div>

<div class="stats-row">
    <div class="card mini-stat primary">
        <div class="mini-stat-icon"><i class="bi bi-box-seam-fill"></i></div>
        <div>
            <div class="mini-stat-value">{{ count($items) }}</div>
            <div class="mini-stat-label">Total Jenis Barang</div>
        </div>
    </div>
    <div class="card mini-stat success">
        <div class="mini-stat-icon"><i class="bi bi-check-circle-fill"></i></div>
        <div>
            <div class="mini-stat-value">{{ $items->sum('current_stock') }}</div>
            <div class="mini-stat-label">Stok Tersedia</div>
        </div>
    </div>
    <div class="card mini-stat danger">
        <div class="mini-stat-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <div>
            <div class="mini-stat-value">{{ $items->where('current_stock', 0)->count() }}</div>
            <div class="mini-stat-label">Stok Habis</div>
        </div>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger mb-4">
        <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

@if($items->count() > 0)
<div class="items-grid">
    @foreach($items as $item)
    <div class="item-card">
        <div class="item-image-wrapper">
            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="item-image">
            @php $stockPercent = $item->total_stock > 0 ? ($item->current_stock / $item->total_stock) * 100 : 0; @endphp
            <span class="item-stock-badge {{ $item->current_stock == 0 ? 'empty' : ($stockPercent <= 30 ? 'low' : 'available') }}">
                {{ $item->current_stock }} tersedia
            </span>
        </div>
        <div class="item-body">
            <h6 class="item-name">{{ $item->name }}</h6>
            <p class="item-desc" title="{{ $item->description }}">{{ $item->description }}</p>
            <div class="item-stats">
                <div class="item-stat">
                    <div class="item-stat-value">{{ $item->total_stock }}</div>
                    <div class="item-stat-label">Total</div>
                </div>
                <div class="item-stat">
                    <div class="item-stat-value">{{ $item->current_stock }}</div>
                    <div class="item-stat-label">Tersedia</div>
                </div>
                <div class="item-stat">
                    <div class="item-stat-value">{{ $item->total_stock - $item->current_stock }}</div>
                    <div class="item-stat-label">Dipinjam</div>
                </div>
            </div>
            <div class="item-actions">
                <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $item->id }}">
                    <i class="bi bi-pencil me-1"></i> Edit
                </button>
                <button class="btn btn-ghost" style="color:var(--danger);border-color:var(--danger)" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $item->id }}">
                    <i class="bi bi-trash me-1"></i> Hapus
                </button>
            </div>
        </div>
    </div>
    @include('items._modals', ['item' => $item])
    @endforeach
</div>
@else
<div class="empty-state">
    <div class="empty-state-icon"><i class="bi bi-box"></i></div>
    <h4>Belum Ada Barang</h4>
    <p>Mulai tambahkan barang ke inventaris lab Anda</p>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="bi bi-plus-lg me-2"></i> Tambah Barang Pertama
    </button>
</div>
@endif

{{-- Add Modal --}}
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('items.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header modal-header-gradient">
                    <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Tambah Barang Baru</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Foto Barang <span class="text-danger">*</span></label>
                        <input type="file" name="image" class="form-control" accept="image/*" required>
                        <small class="text-muted">Format: JPG, PNG, GIF. Maks: 2MB</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Barang <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Sensor DHT11" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Deskripsi singkat barang..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stok Awal <span class="text-danger">*</span></label>
                        <input type="number" name="total_stock" class="form-control" placeholder="10" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection