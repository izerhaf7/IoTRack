<?php

namespace App\Services;

use App\Models\Visit;
use App\Models\Item;
use App\Models\Borrowing;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Service untuk mengelola logika bisnis peminjaman barang.
 * 
 * Service ini menangani:
 * - Pembuatan record peminjaman baru
 * - Pengembalian barang (individual dan batch)
 * - Manajemen stok barang dengan row-level locking
 * 
 * Semua operasi yang melibatkan perubahan stok menggunakan lockForUpdate()
 * untuk mencegah race condition pada akses konkuren (Requirement 12.3).
 */
class BorrowingService
{
    /**
     * Membuat peminjaman baru dengan manajemen stok.
     * 
     * Alur proses:
     * 1. Lock row barang untuk mencegah race condition
     * 2. Validasi ketersediaan stok
     * 3. Buat record peminjaman dengan status 'dipinjam'
     * 4. Kurangi current_stock barang
     * 
     * Menggunakan lockForUpdate() untuk mencegah race condition
     * ketika multiple user meminjam barang yang sama secara bersamaan (Requirement 12.3).
     *
     * @param Visit $visit Instance kunjungan yang melakukan peminjaman
     * @param int $itemId ID barang yang akan dipinjam
     * @param int $quantity Jumlah barang yang akan dipinjam
     * @return Borrowing Instance peminjaman yang berhasil dibuat
     * @throws ValidationException Jika stok barang tidak mencukupi
     */
    public function createBorrowing(Visit $visit, int $itemId, int $quantity): Borrowing
    {
        // Lock row barang untuk mencegah race condition (Requirement 12.3)
        $item = Item::lockForUpdate()->findOrFail($itemId);

        // Validasi ketersediaan stok
        if ($item->current_stock < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'Stok barang ' . $item->name . ' hanya tersisa ' . $item->current_stock . ' unit.'
            ]);
        }

        // Buat record peminjaman dengan status 'dipinjam'
        $borrowing = Borrowing::create([
            'visit_id' => $visit->id,
            'item_id'  => $item->id,
            'quantity' => $quantity,
            'status'   => 'dipinjam',
        ]);

        // Kurangi stok barang
        $item->decrement('current_stock', $quantity);

        return $borrowing;
    }

    /**
     * Mengembalikan satu peminjaman dan memulihkan stok.
     * 
     * Alur proses:
     * 1. Cek apakah peminjaman masih aktif (status = 'dipinjam')
     * 2. Lock row barang untuk mencegah race condition
     * 3. Kembalikan stok (tidak melebihi total_stock)
     * 4. Update status peminjaman menjadi 'dikembalikan'
     * 5. Catat waktu pengembalian
     * 
     * Menggunakan lockForUpdate() untuk mencegah race condition
     * ketika multiple pengembalian terjadi bersamaan (Requirement 12.3).
     * 
     * Stok yang dikembalikan tidak akan melebihi total_stock untuk
     * menjaga integritas data inventaris.
     *
     * @param Borrowing $borrowing Instance peminjaman yang akan dikembalikan
     * @return void
     */
    public function returnBorrowing(Borrowing $borrowing): void
    {
        // Skip jika sudah dikembalikan sebelumnya
        if ($borrowing->status !== 'dipinjam') {
            return;
        }

        // Lock row barang untuk mencegah race condition (Requirement 12.3)
        $item = Item::lockForUpdate()->find($borrowing->item_id);

        if ($item) {
            // Kembalikan stok, pastikan tidak melebihi total_stock
            // Ini mencegah anomali data jika ada kesalahan sebelumnya
            $newStock = $item->current_stock + $borrowing->quantity;
            $item->current_stock = min($newStock, $item->total_stock);
            $item->save();
        }

        // Update status peminjaman dan catat waktu pengembalian
        $borrowing->update([
            'status'      => 'dikembalikan',
            'returned_at' => now(),
        ]);
    }

    /**
     * Mengembalikan semua peminjaman aktif untuk satu kunjungan.
     * 
     * Method ini digunakan saat proses Tap Out untuk mengembalikan
     * semua barang yang dipinjam dalam satu kunjungan sekaligus.
     * 
     * Setiap peminjaman diproses secara individual melalui returnBorrowing()
     * untuk memastikan row-level locking diterapkan dengan benar.
     *
     * @param Visit $visit Instance kunjungan yang akan dikembalikan semua peminjamannya
     * @return void
     */
    public function returnAllForVisit(Visit $visit): void
    {
        // Ambil semua peminjaman aktif untuk kunjungan ini
        $activeBorrowings = Borrowing::where('visit_id', $visit->id)
            ->where('status', 'dipinjam')
            ->get();

        // Kembalikan setiap peminjaman satu per satu
        foreach ($activeBorrowings as $borrowing) {
            $this->returnBorrowing($borrowing);
        }
    }
}
