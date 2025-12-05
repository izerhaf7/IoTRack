{{-- Detail Modal --}}
<div class="modal fade" id="detailModal{{ $visit->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold d-flex align-items-center gap-2">
                    <i class="bi bi-person-badge" style="color: var(--primary)"></i> Detail Kunjungan
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                    <div class="visitor-avatar {{ $visit->purpose }}" style="width:56px;height:56px;font-size:1.25rem">
                        {{ strtoupper(substr($visit->visitor_name, 0, 1)) }}
                    </div>
                    <div>
                        <h5 class="mb-1 fw-bold">{{ $visit->visitor_name }}</h5>
                        <code style="background:var(--bg-hover);padding:0.25rem 0.5rem;border-radius:var(--radius-sm)">{{ $visit->visitor_id }}</code>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <small class="text-muted d-block mb-1">Tujuan</small>
                        <span class="fw-semibold">{{ $visit->purpose === 'belajar' ? 'Belajar' : 'Meminjam Barang' }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block mb-1">Waktu Masuk</small>
                        <span class="fw-semibold">{{ $visit->created_at->format('H:i, d M Y') }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block mb-1">Waktu Keluar</small>
                        <span class="fw-semibold">{{ $visit->tapped_out_at ? $visit->tapped_out_at->format('H:i, d M Y') : '-' }}</span>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block mb-1">Durasi</small>
                        <span class="fw-semibold">
                            @if($visit->tapped_out_at)
                                {{ $visit->created_at->diffForHumans($visit->tapped_out_at, true) }}
                            @else
                                {{ $visit->created_at->diffForHumans(now(), true) }} (masih di lab)
                            @endif
                        </span>
                    </div>
                </div>
                @if($visit->borrowings->count() > 0)
                <div class="mt-4 pt-3 border-top">
                    <h6 class="fw-bold mb-3">Barang Dipinjam</h6>
                    <div class="list-group list-group-flush">
                        @foreach($visit->borrowings as $borrow)
                        <div class="list-group-item d-flex justify-content-between align-items-center px-0" style="background:transparent;border-color:var(--border-light)">
                            <div>
                                <span class="fw-semibold">{{ $borrow->item->name ?? 'Item dihapus' }}</span>
                                <small class="text-muted d-block">{{ $borrow->quantity }} unit</small>
                            </div>
                            @if($borrow->status === 'dipinjam')
                                <span class="badge badge-warning">Dipinjam</span>
                            @else
                                <span class="badge badge-success">Dikembalikan</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteModal{{ $visit->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <div style="width:64px;height:64px;background:rgba(239,68,68,0.15);border-radius:50%;display:inline-flex;align-items:center;justify-content:center">
                        <i class="bi bi-trash fs-2" style="color:var(--danger)"></i>
                    </div>
                </div>
                <h6 class="fw-bold mb-2">Hapus Riwayat?</h6>
                <p class="text-muted small mb-3">Hapus kunjungan <strong>{{ $visit->visitor_name }}</strong>?<br>Tindakan ini tidak dapat dibatalkan.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-ghost px-3" data-bs-dismiss="modal">Batal</button>
                    <form action="{{ route('admin.visit.destroy', $visit->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger px-3">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>