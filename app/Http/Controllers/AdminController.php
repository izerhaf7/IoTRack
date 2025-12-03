<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Models\Borrowing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil data peminjaman yang statusnya masih 'dipinjam'
        $activeBorrowings = Borrowing::where('status', 'dipinjam')
                            ->with(['visit', 'item'])
                            ->get();

        // 2. Filter aktivitas riwayat kunjungan hari ini
        $activityFilter = $request->query('activity'); // null | meminjam | belajar

        $visitsQuery = Visit::whereDate('created_at', now()->today())
                        ->with(['borrowings.item']) // eager-load supaya $visit->borrowings bukan null
                        ->orderBy('created_at', 'desc');

        if ($activityFilter === 'meminjam') {
            $visitsQuery->where('purpose', 'pinjam');
        } elseif ($activityFilter === 'belajar') {
            $visitsQuery->where('purpose', 'belajar');
        }

        // 3. Ambil log kunjungan hari ini (setelah filter + eager load)
        $todaysVisits = $visitsQuery->get();

        // 4. Identifikasi NIM Pengunjung (tidak perlu ikut filter)
        $uniqueVisitorsCount = Visit::whereDate('created_at', now()->today())
                            ->distinct('visitor_id')
                            ->count('visitor_id');

        return view('admin.dashboard', compact(
            'activeBorrowings',
            'todaysVisits',
            'uniqueVisitorsCount',
            'activityFilter'
        ));
    }

    public function returnItem($borrowing_id)
    {
        DB::transaction(function () use ($borrowing_id) {

            $borrowing = Borrowing::with(['item' => function ($q) {
                $q->lockForUpdate();
            }])->findOrFail($borrowing_id);

            if (!$borrowing->item) {
                return;
            }

            if ($borrowing->status === 'dipinjam') {
                $borrowing->update([
                    'status'      => 'dikembalikan',
                    'returned_at' => now(),
                ]);

                $item = $borrowing->item;
                $newStock = $item->current_stock + $borrowing->quantity;

                $item->current_stock = min($newStock, $item->total_stock);
                $item->save();
            }
        });

        return back()->with('success', 'Barang telah dikembalikan dan stok diperbarui.');
    }

    public function destroyVisit($id)
    {
        $visit = Visit::with('borrowings')->findOrFail($id);

        // 1. Cek apakah masih ada peminjaman aktif
        $hasActiveBorrowing = $visit->borrowings->contains('status', 'dipinjam');

        if ($hasActiveBorrowing) {
            return back()->with('error', 'Tidak dapat menghapus riwayat kunjungan yang masih memiliki peminjaman aktif.');
        }

        DB::transaction(function () use ($visit) {
            // 2. Hapus semua borrowing yang terkait (status dikembalikan)
            foreach ($visit->borrowings as $borrow) {
                $borrow->delete(); // kalau pakai SoftDeletes, ini soft delete
            }

            // 3. Baru hapus visit
            $visit->delete();
        });

        return back()->with('success', 'Riwayat kunjungan dan data peminjaman terkait berhasil dihapus.');
    }
}