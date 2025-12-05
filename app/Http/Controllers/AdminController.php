<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Models\Borrowing;
use App\Exports\VisitsExport;
use App\Services\AnalyticsService;
use App\Services\BorrowingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Controller untuk mengelola halaman admin dan operasi administratif.
 */
class AdminController extends Controller
{
    protected AnalyticsService $analyticsService;
    protected BorrowingService $borrowingService;

    public function __construct(
        AnalyticsService $analyticsService,
        BorrowingService $borrowingService
    ) {
        $this->analyticsService = $analyticsService;
        $this->borrowingService = $borrowingService;
    }

    /**
     * Dashboard admin dengan data analitik.
     */
    public function index(Request $request)
    {
        $activeBorrowings = Borrowing::where('status', 'dipinjam')
            ->with(['visit', 'item'])
            ->get();

        $activityFilter = $request->query('activity');

        $visitsQuery = Visit::whereDate('created_at', now()->today())
            ->with(['borrowings.item'])
            ->orderBy('created_at', 'desc');

        if ($activityFilter === 'meminjam') {
            $visitsQuery->where('purpose', 'pinjam');
        } elseif ($activityFilter === 'belajar') {
            $visitsQuery->where('purpose', 'belajar');
        }

        $todaysVisits = $visitsQuery->get();

        $todayStats = $this->analyticsService->getTodayStats();
        $uniqueVisitorsCount = $todayStats['unique_visitors'];

        $mostBorrowedItems = $this->analyticsService->getMostBorrowedItems(7);
        $dailyVisitorCounts = $this->analyticsService->getDailyVisitorCounts(7);
        $purposeDistribution = $this->analyticsService->getPurposeDistribution(7);

        return view('admin.dashboard', compact(
            'activeBorrowings',
            'todaysVisits',
            'uniqueVisitorsCount',
            'activityFilter',
            'mostBorrowedItems',
            'dailyVisitorCounts',
            'purposeDistribution'
        ));
    }

    /**
     * Halaman manajemen peminjaman.
     */
    public function borrowingsIndex(Request $request)
    {
        $status = $request->query('status');
        
        $query = Borrowing::with(['visit', 'item'])->orderBy('created_at', 'desc');
        
        if ($status === 'dipinjam') {
            $query->where('status', 'dipinjam');
        } elseif ($status === 'dikembalikan') {
            $query->where('status', 'dikembalikan');
        }
        
        $borrowings = $query->paginate(15);
        
        // Stats
        $activeBorrowings = Borrowing::where('status', 'dipinjam')->get();
        $returnedToday = Borrowing::where('status', 'dikembalikan')
            ->whereDate('returned_at', now()->today())
            ->count();
        $totalBorrowedItems = Borrowing::where('status', 'dipinjam')->sum('quantity');
        $overdueBorrowings = Borrowing::where('status', 'dipinjam')
            ->where('created_at', '<', now()->subHours(24))
            ->count();
        
        // Counts for tabs
        $allCount = Borrowing::count();
        $returnedCount = Borrowing::where('status', 'dikembalikan')->count();
        
        return view('admin.borrowings.index', compact(
            'borrowings',
            'activeBorrowings',
            'returnedToday',
            'totalBorrowedItems',
            'overdueBorrowings',
            'allCount',
            'returnedCount'
        ));
    }

    /**
     * Halaman manajemen kunjungan.
     */
    public function visitsIndex(Request $request)
    {
        $selectedDate = $request->query('date', now()->format('Y-m-d'));
        $purpose = $request->query('purpose');
        
        $query = Visit::with(['borrowings.item'])
            ->whereDate('created_at', $selectedDate)
            ->orderBy('created_at', 'desc');
        
        if ($purpose) {
            $query->where('purpose', $purpose);
        }
        
        $visits = $query->paginate(15);
        
        // Stats for selected date
        $dateCarbon = Carbon::parse($selectedDate);
        $todayVisits = Visit::whereDate('created_at', $dateCarbon)->count();
        $studyVisits = Visit::whereDate('created_at', $dateCarbon)
            ->where('purpose', 'belajar')
            ->count();
        $borrowVisits = Visit::whereDate('created_at', $dateCarbon)
            ->where('purpose', 'pinjam')
            ->count();
        
        return view('admin.visits.index', compact(
            'visits',
            'selectedDate',
            'todayVisits',
            'studyVisits',
            'borrowVisits'
        ));
    }

    /**
     * Proses pengembalian barang.
     */
    public function returnItem($borrowing_id)
    {
        try {
            DB::transaction(function () use ($borrowing_id) {
                $borrowing = Borrowing::with('item')->findOrFail($borrowing_id);

                if (!$borrowing->item) {
                    throw new \Exception('Barang tidak ditemukan.');
                }

                $this->borrowingService->returnBorrowing($borrowing);
            });

            return back()->with('success', 'Barang telah dikembalikan dan stok diperbarui.');

        } catch (\Exception $e) {
            Log::error('Gagal mengembalikan barang: ' . $e->getMessage());
            return back()->with('error', 'Gagal mengembalikan barang. Silakan coba lagi.');
        }
    }

    /**
     * Hapus riwayat kunjungan.
     */
    public function destroyVisit($id)
    {
        try {
            $visit = Visit::with('borrowings')->findOrFail($id);

            $hasActiveBorrowing = $visit->borrowings->contains('status', 'dipinjam');

            if ($hasActiveBorrowing) {
                return back()->with('error', 'Tidak dapat menghapus riwayat kunjungan yang masih memiliki peminjaman aktif.');
            }

            DB::transaction(function () use ($visit) {
                foreach ($visit->borrowings as $borrow) {
                    $borrow->delete();
                }
                $visit->delete();
            });

            return back()->with('success', 'Riwayat kunjungan berhasil dihapus.');

        } catch (\Exception $e) {
            Log::error('Gagal menghapus kunjungan: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus riwayat kunjungan.');
        }
    }

    /**
     * Ekspor data kunjungan ke Excel.
     */
    public function exportVisits()
    {
        try {
            $export = new VisitsExport();
            $filePath = $export->toExcel();
            $filename = $export->filename();

            return response()->download($filePath, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Gagal mengekspor data kunjungan: ' . $e->getMessage());
            return back()->with('error', 'Gagal membuat file ekspor. Silakan coba lagi.');
        }
    }
}