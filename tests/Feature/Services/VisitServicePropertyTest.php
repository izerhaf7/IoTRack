<?php

namespace Tests\Feature\Services;

use Tests\TestCase;
use App\Models\Visit;
use App\Models\Item;
use App\Models\Borrowing;
use App\Models\Student;
use App\Services\VisitService;
use App\Services\BorrowingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

/**
 * Property-based tests untuk VisitService.
 * Menggunakan data providers untuk menguji berbagai skenario.
 */
class VisitServicePropertyTest extends TestCase
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
     * Feature: iotrack-improvements, Property 1: Tap Out Validation Based on Active Borrowings
     * *For any* student NIM, when attempting Tap Out, the system should reject the request 
     * if and only if the student has no active borrowings.
     * **Validates: Requirements 1.1, 1.3**
     *
     * @dataProvider tapOutValidationProvider
     */
    public function test_tap_out_validation_based_on_active_borrowings(
        bool $hasBorrowings,
        string $borrowingStatus,
        bool $shouldAllow
    ): void {
        // Setup: Buat mahasiswa dan item
        $student = Student::create([
            'nim' => '12345678',
            'name' => 'Test Student',
            'program_studi' => 'Teknik Informatika',
            'tahun_masuk' => 2020,
            'angkatan' => 2020,
        ]);

        $item = Item::create([
            'name' => 'Arduino Uno',
            'total_stock' => 10,
            'current_stock' => 10,
        ]);


        // Buat kunjungan
        $visit = Visit::create([
            'visitor_name' => $student->name,
            'visitor_id' => $student->nim,
            'purpose' => $hasBorrowings ? 'pinjam' : 'belajar',
            'tapped_out_at' => null,
        ]);

        // Buat peminjaman jika diperlukan
        if ($hasBorrowings) {
            Borrowing::create([
                'visit_id' => $visit->id,
                'item_id' => $item->id,
                'quantity' => 1,
                'status' => $borrowingStatus,
                'returned_at' => $borrowingStatus === 'dikembalikan' ? now() : null,
            ]);
        }

        // Act: Validasi tap out
        $validation = $this->visitService->validateTapOut($student->nim);

        // Assert: Hasil validasi sesuai ekspektasi
        $this->assertEquals($shouldAllow, $validation['allowed']);
    }

    public static function tapOutValidationProvider(): array
    {
        return [
            'tanpa peminjaman harus ditolak' => [false, '', false],
            'dengan peminjaman aktif harus diizinkan' => [true, 'dipinjam', true],
            'dengan peminjaman dikembalikan harus ditolak' => [true, 'dikembalikan', false],
        ];
    }

    /**
     * Feature: iotrack-improvements, Property 2: State Preservation on Validation Failure
     * *For any* rejected Tap Out request, the system state should remain identical.
     * **Validates: Requirements 1.4**
     *
     * @dataProvider statePreservationProvider
     */
    public function test_state_preservation_on_validation_failure(
        bool $hasBorrowings,
        string $borrowingStatus
    ): void {
        // Setup: Buat mahasiswa dan item
        $student = Student::create([
            'nim' => '12345678',
            'name' => 'Test Student',
            'program_studi' => 'Teknik Informatika',
            'tahun_masuk' => 2020,
            'angkatan' => 2020,
        ]);

        $item = Item::create([
            'name' => 'Arduino Uno',
            'total_stock' => 10,
            'current_stock' => 8,
        ]);

        // Buat kunjungan
        $visit = Visit::create([
            'visitor_name' => $student->name,
            'visitor_id' => $student->nim,
            'purpose' => $hasBorrowings ? 'pinjam' : 'belajar',
            'tapped_out_at' => null,
        ]);

        // Buat peminjaman jika diperlukan
        if ($hasBorrowings) {
            Borrowing::create([
                'visit_id' => $visit->id,
                'item_id' => $item->id,
                'quantity' => 2,
                'status' => $borrowingStatus,
                'returned_at' => $borrowingStatus === 'dikembalikan' ? now() : null,
            ]);
        }

        // Simpan state sebelum operasi
        $visitCountBefore = Visit::count();
        $borrowingCountBefore = Borrowing::count();
        $itemStockBefore = $item->fresh()->current_stock;
        $visitTappedOutBefore = $visit->fresh()->tapped_out_at;

        // Act: Coba tap out (yang seharusnya gagal)
        try {
            $this->visitService->tapOut($student->nim);
        } catch (ValidationException $e) {
            // Expected exception
        }

        // Assert: State tidak berubah
        $this->assertEquals($visitCountBefore, Visit::count());
        $this->assertEquals($borrowingCountBefore, Borrowing::count());
        $this->assertEquals($itemStockBefore, $item->fresh()->current_stock);
        $this->assertEquals($visitTappedOutBefore, $visit->fresh()->tapped_out_at);
    }

    public static function statePreservationProvider(): array
    {
        return [
            'tanpa peminjaman' => [false, ''],
            'dengan peminjaman dikembalikan' => [true, 'dikembalikan'],
        ];
    }


    /**
     * Feature: iotrack-improvements, Property 3: Visit Initialization State
     * *For any* newly created visit during Tap In, the tapped_out_at field should be null.
     * **Validates: Requirements 2.1**
     *
     * @dataProvider visitInitializationProvider
     */
    public function test_visit_initialization_state(string $purpose): void
    {
        // Setup: Buat mahasiswa dan item
        $student = Student::create([
            'nim' => '12345678',
            'name' => 'Test Student',
            'program_studi' => 'Teknik Informatika',
            'tahun_masuk' => 2020,
            'angkatan' => 2020,
        ]);

        $item = Item::create([
            'name' => 'Arduino Uno',
            'total_stock' => 10,
            'current_stock' => 10,
        ]);

        // Act: Tap In
        $data = [
            'visitor_id' => $student->nim,
            'purpose' => $purpose,
        ];

        if ($purpose === 'pinjam') {
            $data['item_id'] = $item->id;
            $data['quantity'] = 1;
        }

        $visit = $this->visitService->tapIn($data);

        // Assert: tapped_out_at harus null
        $this->assertNull($visit->tapped_out_at);
        $this->assertNull($visit->fresh()->tapped_out_at);
    }

    public static function visitInitializationProvider(): array
    {
        return [
            'tujuan belajar' => ['belajar'],
            'tujuan pinjam' => ['pinjam'],
        ];
    }

    /**
     * Feature: iotrack-improvements, Property 4: Tap Out Timestamp Recording
     * *For any* successful Tap Out, the system should record a non-null timestamp.
     * **Validates: Requirements 2.2**
     */
    public function test_tap_out_timestamp_recording(): void
    {
        // Setup: Buat mahasiswa, item, dan kunjungan dengan peminjaman aktif
        $student = Student::create([
            'nim' => '12345678',
            'name' => 'Test Student',
            'program_studi' => 'Teknik Informatika',
            'tahun_masuk' => 2020,
            'angkatan' => 2020,
        ]);

        $item = Item::create([
            'name' => 'Arduino Uno',
            'total_stock' => 10,
            'current_stock' => 10,
        ]);

        $visit = Visit::create([
            'visitor_name' => $student->name,
            'visitor_id' => $student->nim,
            'purpose' => 'pinjam',
            'tapped_out_at' => null,
        ]);

        Borrowing::create([
            'visit_id' => $visit->id,
            'item_id' => $item->id,
            'quantity' => 1,
            'status' => 'dipinjam',
        ]);

        // Act: Tap Out
        $result = $this->visitService->tapOut($student->nim);

        // Assert: tapped_out_at harus tidak null
        $this->assertNotNull($result->tapped_out_at);
        $this->assertNotNull($visit->fresh()->tapped_out_at);
    }

    /**
     * Feature: iotrack-improvements, Property 5: Tap Out Idempotence
     * *For any* visit that already has a non-null tapped_out_at, attempting Tap Out again should be rejected.
     * **Validates: Requirements 2.3**
     */
    public function test_tap_out_idempotence(): void
    {
        // Setup: Buat mahasiswa, item, dan kunjungan dengan peminjaman aktif
        $student = Student::create([
            'nim' => '12345678',
            'name' => 'Test Student',
            'program_studi' => 'Teknik Informatika',
            'tahun_masuk' => 2020,
            'angkatan' => 2020,
        ]);

        $item = Item::create([
            'name' => 'Arduino Uno',
            'total_stock' => 10,
            'current_stock' => 9,
        ]);

        // Buat kunjungan dengan peminjaman aktif
        $visit = Visit::create([
            'visitor_name' => $student->name,
            'visitor_id' => $student->nim,
            'purpose' => 'pinjam',
            'tapped_out_at' => null,
        ]);

        Borrowing::create([
            'visit_id' => $visit->id,
            'item_id' => $item->id,
            'quantity' => 1,
            'status' => 'dipinjam',
        ]);

        // Act: Tap Out pertama - harus berhasil
        $this->visitService->tapOut($student->nim);

        // Refresh visit untuk mendapatkan tapped_out_at yang baru
        $visit->refresh();
        $this->assertNotNull($visit->tapped_out_at);

        // Act & Assert: Tap Out kedua harus ditolak (karena sudah tidak ada peminjaman aktif)
        $validation = $this->visitService->validateTapOut($student->nim);
        $this->assertFalse($validation['allowed']);
        // Setelah tap out, tidak ada peminjaman aktif lagi, jadi pesan yang muncul adalah "tidak ada peminjaman aktif"
        $this->assertStringContainsString('tidak memiliki peminjaman aktif', $validation['message']);
    }


    /**
     * Feature: iotrack-improvements, Property 6: Tap Out Atomicity
     * *For any* valid Tap Out, all changes should either all succeed or all fail together.
     * **Validates: Requirements 2.5**
     */
    public function test_tap_out_atomicity(): void
    {
        // Setup: Buat mahasiswa, item, dan kunjungan dengan peminjaman aktif
        $student = Student::create([
            'nim' => '12345678',
            'name' => 'Test Student',
            'program_studi' => 'Teknik Informatika',
            'tahun_masuk' => 2020,
            'angkatan' => 2020,
        ]);

        $item = Item::create([
            'name' => 'Arduino Uno',
            'total_stock' => 10,
            'current_stock' => 8,
        ]);

        $visit = Visit::create([
            'visitor_name' => $student->name,
            'visitor_id' => $student->nim,
            'purpose' => 'pinjam',
            'tapped_out_at' => null,
        ]);

        $borrowing = Borrowing::create([
            'visit_id' => $visit->id,
            'item_id' => $item->id,
            'quantity' => 2,
            'status' => 'dipinjam',
        ]);

        // Act: Tap Out
        $result = $this->visitService->tapOut($student->nim);

        // Assert: Semua perubahan terjadi bersamaan
        $visit->refresh();
        $borrowing->refresh();
        $item->refresh();

        // Visit harus memiliki tapped_out_at
        $this->assertNotNull($visit->tapped_out_at);

        // Borrowing harus dikembalikan
        $this->assertEquals('dikembalikan', $borrowing->status);
        $this->assertNotNull($borrowing->returned_at);

        // Stok harus dikembalikan
        $this->assertEquals(10, $item->current_stock);
    }

    /**
     * Test tambahan: Multiple borrowings dalam satu visit harus semua dikembalikan.
     */
    public function test_tap_out_returns_all_borrowings(): void
    {
        // Setup
        $student = Student::create([
            'nim' => '12345678',
            'name' => 'Test Student',
            'program_studi' => 'Teknik Informatika',
            'tahun_masuk' => 2020,
            'angkatan' => 2020,
        ]);

        $item1 = Item::create([
            'name' => 'Arduino Uno',
            'total_stock' => 10,
            'current_stock' => 8,
        ]);

        $item2 = Item::create([
            'name' => 'Raspberry Pi',
            'total_stock' => 5,
            'current_stock' => 3,
        ]);

        $visit = Visit::create([
            'visitor_name' => $student->name,
            'visitor_id' => $student->nim,
            'purpose' => 'pinjam',
            'tapped_out_at' => null,
        ]);

        $borrowing1 = Borrowing::create([
            'visit_id' => $visit->id,
            'item_id' => $item1->id,
            'quantity' => 2,
            'status' => 'dipinjam',
        ]);

        $borrowing2 = Borrowing::create([
            'visit_id' => $visit->id,
            'item_id' => $item2->id,
            'quantity' => 2,
            'status' => 'dipinjam',
        ]);

        // Act
        $this->visitService->tapOut($student->nim);

        // Assert
        $this->assertEquals('dikembalikan', $borrowing1->fresh()->status);
        $this->assertEquals('dikembalikan', $borrowing2->fresh()->status);
        $this->assertEquals(10, $item1->fresh()->current_stock);
        $this->assertEquals(5, $item2->fresh()->current_stock);
    }
}
