<?php

namespace App\Services;

use App\Models\Visit;
use App\Models\Item;
use App\Models\Borrowing;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Service untuk mengelola logika bisnis kunjungan lab.
 * 
 * Service ini menangani:
 * - Proses Tap In (check-in pengunjung)
 * - Proses Tap Out (check-out pengunjung)
 * - Validasi peminjaman aktif
 * - Koordinasi dengan BorrowingService untuk operasi peminjaman
 * 
 * Semua operasi yang melibatkan perubahan data menggunakan database transaction
 * untuk menjamin atomicity (Requirement 12.2).
 */
class VisitService
{
    /**
     * Service untuk operasi peminjaman barang.
     */
    protected BorrowingService $borrowingService;

    /**
     * Konstruktor dengan dependency injection.
     *
     * @param BorrowingService $borrowingService Service untuk operasi peminjaman
     */
    public function __construct(BorrowingService $borrowingService)
    {
        $this->borrowingService = $borrowingService;
    }

    /**
     * Proses Tap In - membuat kunjungan baru dan peminjaman jika diperlukan.
     * 
     * Alur proses:
     * 1. Validasi NIM mahasiswa terdaftar di database
     * 2. Jika tujuan belajar, cek tidak ada tap in belajar yang belum tap out
     * 3. Jika tujuan meminjam, validasi ketersediaan stok
     * 4. Buat record kunjungan dengan tapped_out_at = null
     * 5. Jika meminjam, buat record peminjaman dan kurangi stok
     * 
     * Catatan:
     * - Tap in belajar: Hanya boleh 1x, tidak boleh menumpuk
     * - Tap in meminjam: Boleh berkali-kali
     * 
     * Semua operasi dilakukan dalam satu transaction untuk menjamin atomicity.
     *
     * @param array $data Data kunjungan dengan keys:
     *                    - visitor_id: NIM mahasiswa
     *                    - purpose: 'belajar' atau 'pinjam'
     *                    - item_id: ID barang (wajib jika purpose = 'pinjam')
     *                    - quantity: Jumlah yang dipinjam (wajib jika purpose = 'pinjam')
     * @return Visit Instance kunjungan yang berhasil dibuat
     * @throws ValidationException Jika:
     *                             - NIM tidak terdaftar di database mahasiswa
     *                             - Sudah ada tap in belajar yang belum tap out
     *                             - Barang tidak ditemukan
     *                             - Stok barang tidak mencukupi
     */
    public function tapIn(array $data): Visit
    {
        // Cari mahasiswa berdasarkan NIM
        $student = Student::where('nim', $data['visitor_id'])->first();

        if (!$student) {
            throw ValidationException::withMessages([
                'visitor_id' => 'NIM tidak terdaftar di data mahasiswa.'
            ]);
        }

        // Validasi: Jika tujuan belajar, tidak boleh ada tap in belajar yang belum tap out
        if ($data['purpose'] === 'belajar') {
            $existingBelajar = Visit::where('visitor_id', $data['visitor_id'])
                ->where('purpose', 'belajar')
                ->whereNull('tapped_out_at')
                ->exists();

            if ($existingBelajar) {
                throw ValidationException::withMessages([
                    'purpose' => 'Anda sudah memiliki kunjungan belajar yang belum Tap Out. Silakan Tap Out terlebih dahulu.'
                ]);
            }
        }

        // Validasi stok jika tujuan adalah meminjam
        if ($data['purpose'] === 'pinjam') {
            $item = Item::find($data['item_id']);
            
            if (!$item) {
                throw ValidationException::withMessages([
                    'item_id' => 'Barang tidak ditemukan.'
                ]);
            }

            if ($item->current_stock < $data['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stok barang ' . $item->name . ' hanya tersisa ' . $item->current_stock . ' unit.'
                ]);
            }
        }

        // Simpan visit + borrowing dalam satu transaction
        return DB::transaction(function () use ($data, $student) {
            // Buat kunjungan baru dengan tapped_out_at = null
            $visit = Visit::create([
                'visitor_name' => $student->name,
                'visitor_id'   => $student->nim,
                'purpose'      => $data['purpose'],
                'tapped_out_at' => null,
            ]);

            // Buat peminjaman jika tujuan adalah pinjam
            if ($data['purpose'] === 'pinjam') {
                $this->borrowingService->createBorrowing(
                    $visit,
                    $data['item_id'],
                    $data['quantity']
                );
            }

            return $visit;
        });
    }

    /**
     * Proses Tap Out - tap out SEMUA kunjungan yang belum tap out dan kembalikan SEMUA peminjaman.
     * 
     * Alur proses:
     * 1. Validasi apakah ada kunjungan yang belum tap out
     * 2. Ambil SEMUA kunjungan yang belum tap out untuk NIM tersebut
     * 3. Kembalikan SEMUA peminjaman aktif dari semua kunjungan
     * 4. Catat waktu tap out pada SEMUA record kunjungan
     * 
     * Catatan: Sekali tap out, semua log kunjungan dan peminjaman akan selesai.
     * 
     * Semua operasi dilakukan dalam satu transaction.
     *
     * @param string $nim NIM mahasiswa yang akan tap out
     * @return Visit Instance kunjungan terbaru yang berhasil di-tap out
     * @throws ValidationException Jika:
     *                             - Tidak ada kunjungan yang belum tap out
     *                             - Data kunjungan tidak ditemukan
     */
    public function tapOut(string $nim): Visit
    {
        // Validasi apakah tap out diizinkan
        $validation = $this->validateTapOut($nim);

        if (!$validation['allowed']) {
            throw ValidationException::withMessages([
                'visitor_id' => $validation['message']
            ]);
        }

        // Ambil SEMUA kunjungan yang belum tap out
        $visits = $validation['visits'];

        // Proses tap out dalam transaction
        return DB::transaction(function () use ($visits) {
            $now = now();
            $latestVisit = null;

            foreach ($visits as $visit) {
                // Kembalikan semua peminjaman aktif untuk kunjungan ini
                $this->borrowingService->returnAllForVisit($visit);

                // Catat waktu tap out
                $visit->update([
                    'tapped_out_at' => $now
                ]);

                $latestVisit = $visit;
            }

            return $latestVisit->fresh();
        });
    }

    /**
     * Validasi apakah Tap Out diizinkan untuk NIM tertentu.
     * 
     * Kriteria validasi:
     * 1. Harus ada data kunjungan untuk NIM tersebut
     * 2. Harus ada kunjungan yang belum tap out (tapped_out_at = null)
     *
     * @param string $nim NIM mahasiswa
     * @return array Hasil validasi dengan format:
     *               - allowed: bool - apakah tap out diizinkan
     *               - message: string - pesan error jika tidak diizinkan
     *               - visits: Collection|null - semua kunjungan yang belum tap out
     */
    public function validateTapOut(string $nim): array
    {
        // Cari SEMUA kunjungan yang belum tap out berdasarkan NIM
        $visits = Visit::where('visitor_id', $nim)
            ->whereNull('tapped_out_at')
            ->with(['borrowings' => function ($query) {
                $query->where('status', 'dipinjam');
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        // Validasi: harus ada kunjungan yang belum tap out
        if ($visits->isEmpty()) {
            // Cek apakah ada data kunjungan sama sekali
            $hasAnyVisit = Visit::where('visitor_id', $nim)->exists();
            
            if (!$hasAnyVisit) {
                return [
                    'allowed' => false,
                    'message' => 'Data kunjungan tidak ditemukan untuk NIM tersebut.',
                    'visits' => null
                ];
            }
            
            return [
                'allowed' => false,
                'message' => 'Semua kunjungan Anda sudah di-checkout. Silakan Tap In terlebih dahulu.',
                'visits' => null
            ];
        }

        return [
            'allowed' => true,
            'message' => 'Tap Out diizinkan.',
            'visits' => $visits
        ];
    }
}
