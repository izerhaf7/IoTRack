<?php

namespace App\Services;

use App\Models\Visit;
use App\Models\Borrowing;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Service untuk mengelola analitik dan statistik dashboard.
 * 
 * Service ini menyediakan data untuk:
 * - Grafik barang paling sering dipinjam (bar chart)
 * - Grafik jumlah pengunjung harian (line chart)
 * - Grafik distribusi tujuan kunjungan (pie chart)
 * - Metrik statistik hari ini (cards)
 * 
 * Semua method menerima parameter periode waktu dalam hari
 * untuk fleksibilitas dalam menampilkan data historis.
 */
class AnalyticsService
{
    /**
     * Mendapatkan daftar barang yang paling sering dipinjam.
     * 
     * Query ini menghitung jumlah peminjaman per barang dalam periode tertentu,
     * diurutkan dari yang paling sering dipinjam.
     * 
     * Hasil dibatasi maksimal 10 barang untuk tampilan chart yang optimal.
     *
     * @param int $days Periode waktu dalam hari (default 7 hari)
     * @return Collection Koleksi dengan format:
     *                    [['item_name' => string, 'borrow_count' => int], ...]
     *                    Diurutkan dari borrow_count tertinggi
     */
    public function getMostBorrowedItems(int $days = 7): Collection
    {
        $startDate = Carbon::now()->subDays($days)->startOfDay();

        return Borrowing::select('items.name as item_name', DB::raw('COUNT(*) as borrow_count'))
            ->join('items', 'borrowings.item_id', '=', 'items.id')
            ->where('borrowings.created_at', '>=', $startDate)
            ->groupBy('items.id', 'items.name')
            ->orderByDesc('borrow_count')
            ->limit(10)
            ->get();
    }

    /**
     * Mendapatkan jumlah pengunjung unik per hari.
     * 
     * Query ini menghitung jumlah NIM unik yang berkunjung setiap hari.
     * Satu mahasiswa yang berkunjung beberapa kali dalam sehari
     * hanya dihitung sekali (Requirement 6.2).
     * 
     * Hasil mencakup semua tanggal dalam periode, termasuk tanggal
     * tanpa kunjungan (ditampilkan sebagai 0) untuk konsistensi chart.
     *
     * @param int $days Periode waktu dalam hari (default 7 hari)
     * @return Collection Koleksi dengan format:
     *                    [['date' => 'YYYY-MM-DD', 'visitor_count' => int], ...]
     *                    Diurutkan dari tanggal terlama ke terbaru
     */
    public function getDailyVisitorCounts(int $days = 7): Collection
    {
        $startDate = Carbon::now()->subDays($days - 1)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // Ambil data pengunjung unik per hari dari database
        $visitorData = Visit::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(DISTINCT visitor_id) as visitor_count')
            )
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->groupBy(DB::raw('DATE(created_at)'))
            ->get()
            ->keyBy('date');

        // Buat array untuk semua tanggal dalam periode
        // Ini memastikan tanggal tanpa kunjungan tetap ditampilkan sebagai 0
        $result = collect();
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $result->push([
                'date' => $date,
                'visitor_count' => $visitorData->has($date) 
                    ? (int) $visitorData->get($date)->visitor_count 
                    : 0
            ]);
        }

        return $result;
    }

    /**
     * Mendapatkan distribusi tujuan kunjungan.
     * 
     * Query ini menghitung jumlah kunjungan berdasarkan tujuan
     * (belajar atau pinjam) dalam periode tertentu.
     * 
     * Hasil selalu mencakup kedua kategori, dengan nilai 0
     * jika tidak ada kunjungan untuk kategori tersebut.
     *
     * @param int $days Periode waktu dalam hari (default 7 hari)
     * @return array Format: ['belajar' => int, 'pinjam' => int]
     *               Nilai adalah jumlah kunjungan per kategori
     */
    public function getPurposeDistribution(int $days = 7): array
    {
        $startDate = Carbon::now()->subDays($days)->startOfDay();

        $distribution = Visit::select('purpose', DB::raw('COUNT(*) as count'))
            ->where('created_at', '>=', $startDate)
            ->groupBy('purpose')
            ->get()
            ->pluck('count', 'purpose')
            ->toArray();

        // Pastikan kedua kategori selalu ada dalam hasil
        return [
            'belajar' => $distribution['belajar'] ?? 0,
            'pinjam' => $distribution['pinjam'] ?? 0,
        ];
    }

    /**
     * Mendapatkan statistik hari ini untuk dashboard.
     * 
     * Method ini menyediakan data untuk metric cards di bagian atas dashboard:
     * - Jumlah pengunjung unik hari ini
     * - Jumlah peminjaman yang masih aktif (belum dikembalikan)
     *
     * @return array Format: [
     *               'unique_visitors' => int,    // Pengunjung unik hari ini
     *               'active_borrowings' => int   // Peminjaman aktif saat ini
     *               ]
     */
    public function getTodayStats(): array
    {
        $today = Carbon::today();

        // Hitung pengunjung unik hari ini (berdasarkan NIM)
        $uniqueVisitors = Visit::whereDate('created_at', $today)
            ->distinct('visitor_id')
            ->count('visitor_id');

        // Hitung peminjaman aktif (status = 'dipinjam')
        $activeBorrowings = Borrowing::where('status', 'dipinjam')->count();

        return [
            'unique_visitors' => $uniqueVisitors,
            'active_borrowings' => $activeBorrowings,
        ];
    }
}
