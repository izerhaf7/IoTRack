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
     * 2. Jika tujuan adalah meminjam, validasi ketersediaan stok
     * 3. Buat record kunjungan dengan tapped_out_at = null (Requirement 2.1)
     * 4. Jika meminjam, buat record peminjaman dan kurangi stok
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

        // Simpan visit + borrowing dalam satu transaction (Requirement 12.2)
        return DB::transaction(function () use ($data, $student) {
            // Buat kunjungan baru dengan tapped_out_at = null (Requirement 2.1)
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
     * Proses Tap Out - validasi dan kembalikan semua peminjaman aktif.
     * 
     * Alur proses:
     * 1. Validasi apakah Tap Out diizinkan (ada peminjaman aktif, belum tap out)
     * 2. Kembalikan semua peminjaman aktif melalui BorrowingService
     * 3. Catat waktu tap out pada record kunjungan (Requirement 2.2)
     * 
     * Validasi yang dilakukan:
     * - Requirement 1.1, 1.3: Harus ada peminjaman aktif
     * - Requirement 2.3: Belum pernah tap out sebelumnya
     * 
     * Semua operasi dilakukan dalam satu transaction (Requirement 2.5).
     *
     * @param string $nim NIM mahasiswa yang akan tap out
     * @return Visit Instance kunjungan yang berhasil di-tap out
     * @throws ValidationException Jika:
     *                             - Tidak ada peminjaman aktif (Requirement 1.1)
     *                             - Kunjungan sudah di-tap out sebelumnya (Requirement 2.3)
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

        // Ambil kunjungan yang eligible untuk tap out
        $visit = $validation['visit'];

        // Proses tap out dalam transaction (Requirement 2.5)
        return DB::transaction(function () use ($visit) {
            // Kembalikan semua peminjaman aktif
            $this->borrowingService->returnAllForVisit($visit);

            // Catat waktu tap out (Requirement 2.2)
            $visit->update([
                'tapped_out_at' => now()
            ]);

            return $visit->fresh();
        });
    }

    /**
     * Validasi apakah Tap Out diizinkan untuk NIM tertentu.
     * 
     * Kriteria validasi:
     * 1. Harus ada data kunjungan untuk NIM tersebut
     * 2. Harus ada peminjaman aktif (status = 'dipinjam') - Requirement 1.1, 1.3
     * 3. Kunjungan belum pernah di-tap out (tapped_out_at = null) - Requirement 2.3
     *
     * @param string $nim NIM mahasiswa
     * @return array Hasil validasi dengan format:
     *               - allowed: bool - apakah tap out diizinkan
     *               - message: string - pesan error jika tidak diizinkan
     *               - visit: Visit|null - instance kunjungan yang eligible
     */
    public function validateTapOut(string $nim): array
    {
        // Cari semua kunjungan berdasarkan NIM dengan eager loading peminjaman aktif
        $visits = Visit::where('visitor_id', $nim)
            ->with(['borrowings' => function ($query) {
                $query->where('status', 'dipinjam');
            }])
            ->get();

        // Validasi: data kunjungan harus ada
        if ($visits->isEmpty()) {
            return [
                'allowed' => false,
                'message' => 'Data kunjungan tidak ditemukan untuk NIM tersebut.',
                'visit' => null
            ];
        }

        // Cari kunjungan yang eligible untuk tap out
        $eligibleVisit = null;
        $hasActiveBorrowings = false;
        $alreadyTappedOut = false;

        foreach ($visits as $visit) {
            // Cek apakah ada peminjaman aktif
            $activeBorrowings = $visit->borrowings->where('status', 'dipinjam');
            
            if ($activeBorrowings->isNotEmpty()) {
                $hasActiveBorrowings = true;
                
                // Cek apakah sudah tap out (Requirement 2.3)
                if ($visit->tapped_out_at !== null) {
                    $alreadyTappedOut = true;
                } else {
                    // Kunjungan ini eligible untuk tap out
                    $eligibleVisit = $visit;
                    break;
                }
            }
        }

        // Validasi: harus ada peminjaman aktif (Requirement 1.1, 1.3)
        if (!$hasActiveBorrowings) {
            return [
                'allowed' => false,
                'message' => 'Anda tidak memiliki peminjaman aktif untuk dikembalikan, Tap Out tidak diizinkan.',
                'visit' => null
            ];
        }

        // Validasi: belum pernah tap out (Requirement 2.3, 2.4)
        if ($alreadyTappedOut && $eligibleVisit === null) {
            return [
                'allowed' => false,
                'message' => 'Kunjungan ini sudah di-checkout. Tap Out berulang tidak diizinkan.',
                'visit' => null
            ];
        }

        return [
            'allowed' => true,
            'message' => 'Tap Out diizinkan.',
            'visit' => $eligibleVisit
        ];
    }
}
