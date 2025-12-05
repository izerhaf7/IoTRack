{{-- Edit Modal --}}
<div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('items.update', $item->id) }}" method="POST" enctype="multipart/form-data">
                @csrf @method('PUT')
                <div class="modal-header modal-header-gradient">
                    <h6 class="modal-title fw-bold"><i class="bi bi-pencil me-2"></i>Edit {{ $item->name }}</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <img src="{{ asset('storage/' . $item->image) }}" class="rounded" style="height:100px;width:auto;object-fit:cover">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ganti Gambar</label>
                        <input type="file" name="image" class="form-control form-control-sm" accept="image/*">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Barang</label>
                        <input type="text" name="name" class="form-control" value="{{ $item->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="2" required>{{ $item->description }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label">Total Aset</label>
                            <input type="number" name="total_stock" class="form-control" value="{{ $item->total_stock }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Stok Tersedia</label>
                            <input type="number" name="current_stock" class="form-control" value="{{ $item->current_stock }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-ghost" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
<div class="modal fade" id="deleteModal{{ $item->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="mb-3">
                    <div style="width:64px;height:64px;background:rgba(239,68,68,0.15);border-radius:50%;display:inline-flex;align-items:center;justify-content:center">
                        <i class="bi bi-trash fs-2" style="color:var(--danger)"></i>
                    </div>
                </div>
                <h6 class="fw-bold mb-2">Hapus {{ $item->name }}?</h6>
                <p class="text-muted small mb-3">Tindakan ini tidak dapat dibatalkan.</p>
                <div class="d-flex gap-2 justify-content-center">
                    <button type="button" class="btn btn-ghost px-3" data-bs-dismiss="modal">Batal</button>
                    <form action="{{ route('items.destroy', $item->id) }}" method="POST">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger px-3">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>