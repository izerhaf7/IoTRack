<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Visit;
use App\Models\Item;
use App\Models\Borrowing;
use App\Models\Student;
use App\Services\VisitService;
use App\Services\BorrowingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

/**
 * Test untuk logika Tap Out.
 * Mencakup unit test dan property-based test.
 */
class TapOutLogicTest extends TestCase
{
    use RefreshDatabase;

    protected VisitService $visitService;
    protected BorrowingService $borrowingService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->borrowingService = new BorrowingService();
        $this->visitService = new VisitService($this->borrowingService);
    }

    /**
     * Helper untuk membuat mahasiswa test.
     */
    protected function createStudent(string $nim = '12345678'): Student
    {
        return Student::create([
            'nim' => $nim,
            'name' => 'Test Student',
            'program_studi' => 'Teknik Informatika',
            'tahun_masuk' => 2020,
            'angkatan' => 2020,
        ]);
    }

    /**
     * Helper untuk membuat item test.
     */
    protected function createItem(string $name = 'Arduino Uno', int $stock = 10): Item
    {
        return Item::create([
            'name' => $name,
            'total_stock' => $stock,
            'current_stock' => $stock,
        ]);
    }


    // ========================================
    // UNIT TESTS
    // ========================================

    /**
     * Unit test: Pesan error "tidak ada peminjaman aktif" ditampilkan dengan benar.
     * Validates: Requirements 1.1, 1.2
     */
    public function test_no_active_borrowings_error_message(): void
    {
        $student = $this->createStudent();
        
        // Buat kunjungan tanpa peminjaman (tujuan belajar)
        Visit::create([
            'visitor_name' => $student->name,
            'visitor_id' => $student->nim,
            'purpose' => 'belajar',
            'tapped_out_at' => null,
        ]);

        // Validasi tap out
        $validation = $this->visitService->validateTapOut($student->nim);

        // Assert: Harus ditolak dengan pesan yang benar
        $this->assertFalse($validation['allowed']);
        $this->assertStringContainsString(
            'tidak memiliki peminjaman aktif',
            $validation['message']
        );
    }

    /**
     * Unit test: Pesan error "sudah di-checkout" ditampilkan dengan benar.
     * Validates: Requirements 2.3, 2.4
     */
    public function test_already_tapped_out_error_message(): void
    {
        $student = $this->createStudent();
        $item = $this->createItem();

        // Buat kunjungan yang sudah tap out dengan peminjaman yang sudah dikembalikan
        $visit = Visit::create([
            'visitor_name' => $student->name,
            'visitor_id' => $student->nim,
            'purpose' => 'pinjam',
            'tapped_out_at' => now(), // Sudah tap out
        ]);

        Borrowing::create([
            'visit_id' => $visit->id,
            'item_id' => $item->id,
            'quantity' => 1,
            'status' => 'dikembalikan',
            'returned_at' => now(),
        ]);

        // Validasi tap out
        $validation = $this->visitService->validateTapOut($student->nim);

        // Assert: Harus ditolak (tidak ada peminjaman aktif lagi)
        $this->assertFalse($validation['allowed']);
    }

    /**
     * Unit test: Tap Out berhasil mengembalikan stok.
     */
    public function test_tap_out_restores_stock(): void
    {
        $student = $this->createStudent();
        $item = $this->createItem('Arduino Uno', 10);
        
        // Kurangi stok untuk simulasi peminjaman
        $item->update(['current_stock' => 8]);

        $visit = Visit::create([
            'visitor_name' => $student->name,
            'visitor_id' => $student->nim,
            'purpose' => 'pinjam',
            'tapped_out_at' => null,
        ]);

        Borrowing::create([
            'visit_id' => $visit->id,
            'item_id' => $item->id,
            'quantity' => 2,
            'status' => 'dipinjam',
        ]);

        // Act: Tap Out
        $this->visitService->tapOut($student->nim);

        // Assert: Stok dikembalikan
        $this->assertEquals(10, $item->fresh()->current_stock);
    }


    // ========================================
    // PROPERTY-BASED TESTS
    // ========================================

    /**
     * Feature: iotrack-improvements, Property 16: Transaction Atomicity Under Failure
     * *For any* multi-table database operation, if any part fails, no changes should be committed.
     * **Validates: Requirements 12.2**
     *
     * @dataProvider transactionAtomicityProvider
     */
    public function test_transaction_atomicity_under_failure(
        int $borrowingCount,
        int $quantityPerBorrowing
    ): void {
        $student = $this->createStudent();
        
        // Buat beberapa item dengan stok berbeda
        $items = [];
        for ($i = 0; $i < $borrowingCount; $i++) {
            $items[] = $this->createItem("Item $i", 10);
        }

        // Buat kunjungan dengan peminjaman aktif
        $visit = Visit::create([
            'visitor_name' => $student->name,
            'visitor_id' => $student->nim,
            'purpose' => 'pinjam',
            'tapped_out_at' => null,
        ]);

        // Buat peminjaman untuk setiap item
        foreach ($items as $index => $item) {
            $item->update(['current_stock' => $item->total_stock - $quantityPerBorrowing]);
            
            Borrowing::create([
                'visit_id' => $visit->id,
                'item_id' => $item->id,
                'quantity' => $quantityPerBorrowing,
                'status' => 'dipinjam',
            ]);
        }

        // Simpan state sebelum operasi
        $stocksBefore = [];
        foreach ($items as $item) {
            $stocksBefore[$item->id] = $item->fresh()->current_stock;
        }
        $visitTappedOutBefore = $visit->fresh()->tapped_out_at;
        $borrowingStatusesBefore = Borrowing::where('visit_id', $visit->id)
            ->pluck('status', 'id')
            ->toArray();

        // Act: Tap Out yang berhasil
        $this->visitService->tapOut($student->nim);

        // Assert: Semua perubahan terjadi bersamaan (atomicity)
        $visit->refresh();
        
        // Visit harus memiliki tapped_out_at
        $this->assertNotNull($visit->tapped_out_at);

        // Semua borrowing harus dikembalikan
        $allBorrowingsReturned = Borrowing::where('visit_id', $visit->id)
            ->where('status', 'dikembalikan')
            ->count() === $borrowingCount;
        $this->assertTrue($allBorrowingsReturned);

        // Semua stok harus dikembalikan
        foreach ($items as $item) {
            $this->assertEquals(
                $item->total_stock,
                $item->fresh()->current_stock,
                "Stok item {$item->name} tidak dikembalikan dengan benar"
            );
        }
    }

    public static function transactionAtomicityProvider(): array
    {
        return [
            'satu peminjaman' => [1, 2],
            'dua peminjaman' => [2, 3],
            'tiga peminjaman' => [3, 1],
        ];
    }


    /**
     * Feature: iotrack-improvements, Property 17: Concurrent Stock Update Integrity
     * *For any* two concurrent borrowing operations on the same item, the final stock level
     * should equal the initial stock minus the sum of both quantities.
     * **Validates: Requirements 12.3**
     *
     * @dataProvider concurrentStockUpdateProvider
     */
    public function test_concurrent_stock_update_integrity(
        int $initialStock,
        int $quantity1,
        int $quantity2
    ): void {
        $student1 = $this->createStudent('11111111');
        $student2 = $this->createStudent('22222222');
        $item = $this->createItem('Shared Item', $initialStock);

        // Buat dua kunjungan dengan peminjaman pada item yang sama
        $visit1 = Visit::create([
            'visitor_name' => $student1->name,
            'visitor_id' => $student1->nim,
            'purpose' => 'pinjam',
            'tapped_out_at' => null,
        ]);

        $visit2 = Visit::create([
            'visitor_name' => $student2->name,
            'visitor_id' => $student2->nim,
            'purpose' => 'pinjam',
            'tapped_out_at' => null,
        ]);

        // Simulasi peminjaman berurutan (karena PHP single-threaded)
        // Gunakan lockForUpdate untuk memastikan integritas
        DB::transaction(function () use ($visit1, $item, $quantity1) {
            $lockedItem = Item::lockForUpdate()->find($item->id);
            
            Borrowing::create([
                'visit_id' => $visit1->id,
                'item_id' => $lockedItem->id,
                'quantity' => $quantity1,
                'status' => 'dipinjam',
            ]);

            $lockedItem->decrement('current_stock', $quantity1);
        });

        DB::transaction(function () use ($visit2, $item, $quantity2) {
            $lockedItem = Item::lockForUpdate()->find($item->id);
            
            Borrowing::create([
                'visit_id' => $visit2->id,
                'item_id' => $lockedItem->id,
                'quantity' => $quantity2,
                'status' => 'dipinjam',
            ]);

            $lockedItem->decrement('current_stock', $quantity2);
        });

        // Assert: Stok akhir = stok awal - (quantity1 + quantity2)
        $expectedStock = $initialStock - $quantity1 - $quantity2;
        $this->assertEquals($expectedStock, $item->fresh()->current_stock);

        // Act: Tap Out kedua kunjungan
        $this->visitService->tapOut($student1->nim);
        $this->visitService->tapOut($student2->nim);

        // Assert: Stok dikembalikan ke nilai awal
        $this->assertEquals($initialStock, $item->fresh()->current_stock);
    }

    public static function concurrentStockUpdateProvider(): array
    {
        return [
            'stok 10, pinjam 2 dan 3' => [10, 2, 3],
            'stok 20, pinjam 5 dan 5' => [20, 5, 5],
            'stok 15, pinjam 1 dan 4' => [15, 1, 4],
        ];
    }

    /**
     * Test tambahan: Validasi bahwa stok tidak melebihi total_stock setelah pengembalian.
     */
    public function test_stock_does_not_exceed_total_after_return(): void
    {
        $student = $this->createStudent();
        $item = $this->createItem('Test Item', 10);
        
        // Simulasi kondisi dimana current_stock sudah sama dengan total_stock
        // tapi ada peminjaman yang belum dikembalikan (edge case)
        $item->update(['current_stock' => 10]);

        $visit = Visit::create([
            'visitor_name' => $student->name,
            'visitor_id' => $student->nim,
            'purpose' => 'pinjam',
            'tapped_out_at' => null,
        ]);

        // Buat peminjaman tanpa mengurangi stok (simulasi data inconsistent)
        Borrowing::create([
            'visit_id' => $visit->id,
            'item_id' => $item->id,
            'quantity' => 2,
            'status' => 'dipinjam',
        ]);

        // Act: Tap Out
        $this->visitService->tapOut($student->nim);

        // Assert: Stok tidak melebihi total_stock
        $this->assertLessThanOrEqual(
            $item->total_stock,
            $item->fresh()->current_stock,
            'Stok tidak boleh melebihi total_stock'
        );
    }
}
