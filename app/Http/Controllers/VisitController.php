<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Models\Item;
use App\Http\Requests\TapInRequest;
use App\Http\Requests\TapOutRequest;
use App\Services\VisitService;
use Illuminate\Validation\ValidationException;

/**
 * Controller untuk mengelola kunjungan lab (Tap In dan Tap Out).
 * Controller ini hanya menangani HTTP concerns, logika bisnis didelegasikan ke VisitService.
 */
class VisitController extends Controller
{
    /**
     * Service untuk logika bisnis kunjungan.
     */
    protected VisitService $visitService;

    /**
     * Konstruktor dengan dependency injection.
     *
     * @param VisitService $visitService
     */
    public function __construct(VisitService $visitService)
    {
        $this->visitService = $visitService;
    }

    /**
     * Menampilkan form Tap In.
     * Mengambil daftar barang yang tersedia untuk dipinjam.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        // Ambil barang dengan stok tersedia
        $items = Item::where('current_stock', '>', 0)->get();

        return view('visit.form', compact('items'));
    }

    /**
     * Memproses Tap In menggunakan VisitService.
     * Validasi dilakukan oleh TapInRequest.
     *
     * @param TapInRequest $request Request yang sudah divalidasi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(TapInRequest $request)
    {
        try {
            // Delegasikan ke VisitService untuk proses Tap In
            $visit = $this->visitService->tapIn($request->validated());

            return redirect()->route('visit.welcome', $visit->id);

        } catch (ValidationException $e) {
            // Tangkap error validasi dari service (NIM tidak terdaftar, stok tidak cukup)
            $errors = $e->errors();
            $errorMessage = collect($errors)->flatten()->first() ?? 'Terjadi kesalahan saat proses Tap In.';
            
            return back()
                ->withInput()
                ->with('error', $errorMessage);
        }
    }

    /**
     * Menampilkan halaman sambutan setelah Tap In berhasil.
     *
     * @param Visit $visit Instance kunjungan
     * @return \Illuminate\View\View
     */
    public function welcome(Visit $visit)
    {
        return view('visit.welcome', compact('visit'));
    }

    /**
     * Menampilkan form Tap Out.
     *
     * @return \Illuminate\View\View
     */
    public function tapOutForm()
    {
        return view('visit.tap-out');
    }

    /**
     * Memproses Tap Out menggunakan VisitService.
     * Validasi dilakukan oleh TapOutRequest dan VisitService.
     * 
     * Requirements: 1.1, 1.2, 1.3, 1.4, 2.3, 2.4, 2.5, 10.4
     *
     * @param TapOutRequest $request Request yang sudah divalidasi
     * @return \Illuminate\Http\RedirectResponse
     */
    public function tapOutProcess(TapOutRequest $request)
    {
        try {
            // Delegasikan ke VisitService untuk proses Tap Out dengan validasi lengkap
            // - Validasi peminjaman aktif (Requirement 1.1, 1.3)
            // - Validasi duplikat Tap Out (Requirement 2.3)
            // - Proses pengembalian dalam transaksi (Requirement 2.5)
            $visit = $this->visitService->tapOut($request->validated()['visitor_id']);

            return redirect()->route('visit.goodbye', ['visitor_id' => $request->visitor_id]);

        } catch (ValidationException $e) {
            // Tampilkan pesan error dalam Bahasa Indonesia (Requirement 10.4)
            $errors = $e->errors();
            $errorMessage = $errors['visitor_id'][0] ?? 'Terjadi kesalahan saat proses Tap Out.';
            
            return back()
                ->withInput()
                ->with('error', $errorMessage);
        }
    }

    /**
     * Menampilkan halaman ucapan setelah Tap Out berhasil.
     *
     * @param string $visitor_id NIM pengunjung
     * @return \Illuminate\View\View
     */
    public function goodbye($visitor_id)
    {
        // Ambil kunjungan terakhir untuk mendapatkan nama pengunjung
        $latestVisit = Visit::where('visitor_id', $visitor_id)->latest()->first();

        $name = $latestVisit?->visitor_name ?? 'Pengunjung';

        return view('visit.goodbye', compact('name'));
    }
}
