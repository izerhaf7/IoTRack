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

/**
 * Test untuk fitur QR Code Scanning pada Tap In.
 * Mencakup unit test dan property-based test.
 */
class QrScanningTest extends TestCase
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
    protected function createStudent(string $nim = '12345678', string $name = 'Test Student'): Student
    {
        return Student::create([
            'nim' => $nim,
            'name' => $name,
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
     * Unit test: Tombol scan QR ada di halaman Tap In.
     * Validates: Requirements 4.1
     */
    public function test_qr_scan_button_is_present_on_tap_in_page(): void
    {
        $response = $this->get(route('visit.create'));

        $response->assertStatus(200);
        // Verifikasi tombol QR scan ada
        $response->assertSee('id="qrScanBtn"', false);
        $response->assertSee('bi-qr-code-scan', false);
    }

    /**
     * Unit test: Container preview kamera ada di halaman Tap In (hidden by default).
     * Validates: Requirements 4.2, 4.3
     */
    public function test_camera_preview_container_exists_hidden_by_default(): void
    {
        $response = $this->get(route('visit.create'));

        $response->assertStatus(200);
        // Verifikasi container scanner ada
        $response->assertSee('id="qrScannerContainer"', false);
        $response->assertSee('id="qrReader"', false);
        // Verifikasi container hidden by default (d-none class)
        $response->assertSee('qrScannerContainer" class="mb-3 d-none"', false);
    }

    /**
     * Unit test: Library html5-qrcode dimuat di halaman Tap In.
     * Validates: Requirements 4.2
     */
    public function test_html5_qrcode_library_is_loaded(): void
    {
        $response = $this->get(route('visit.create'));

        $response->assertStatus(200);
        // Verifikasi library dimuat via CDN
        $response->assertSee('html5-qrcode', false);
    }

    /**
     * Unit test: Input NIM masih bisa diisi manual.
     * Validates: Requirements 4.1, 4.5
     */
    public function test_manual_nim_input_still_works(): void
    {
        $response = $this->get(route('visit.create'));

        $response->assertStatus(200);
        // Verifikasi input NIM ada
        $response->assertSee('id="nimInput"', false);
        $response->assertSee('name="visitor_id"', false);
    }

    /**
     * Unit test: Pesan error dalam Bahasa Indonesia untuk kamera ditolak.
     * Validates: Requirements 4.6, 10.1
     */
    public function test_error_messages_are_in_bahasa_indonesia(): void
    {
        $response = $this->get(route('visit.create'));

        $response->assertStatus(200);
        // Verifikasi pesan error dalam Bahasa Indonesia ada di JavaScript
        $response->assertSee('Akses kamera ditolak', false);
        $response->assertSee('masukkan NIM secara manual', false);
    }


    // ========================================
    // PROPERTY-BASED TESTS
    // ========================================

    /**
     * Feature: iotrack-improvements, Property 9: QR Code to Form Field Mapping
     * *For any* valid QR code payload containing a NIM, scanning should populate
     * the NIM input field with the exact decoded value.
     * **Validates: Requirements 4.4**
     *
     * Note: Since QR scanning is a client-side JavaScript feature, we test the
     * server-side behavior that the NIM field value is correctly processed
     * regardless of how it was populated (QR or manual).
     *
     * @dataProvider qrCodeNimProvider
     */
    public function test_qr_code_to_form_field_mapping(string $nim): void
    {
        // Setup: Buat mahasiswa dengan NIM yang akan di-scan
        $student = $this->createStudent($nim, 'Student ' . $nim);
        $item = $this->createItem();

        // Act: Submit form dengan NIM (simulasi hasil scan QR)
        // Ini memverifikasi bahwa nilai NIM yang di-decode dari QR
        // akan diproses dengan benar oleh server
        $response = $this->post(route('visit.store'), [
            'visitor_id' => $nim,
            'purpose' => 'belajar',
        ]);

        // Assert: Visit dibuat dengan NIM yang benar
        $this->assertDatabaseHas('visits', [
            'visitor_id' => $nim,
            'visitor_name' => 'Student ' . $nim,
        ]);
    }

    public static function qrCodeNimProvider(): array
    {
        return [
            'NIM standar' => ['J0404241017'],
            'NIM dengan huruf kecil' => ['j0404241017'],
            'NIM numerik' => ['12345678901'],
            'NIM dengan dash' => ['J04-042-41017'],
            'NIM pendek' => ['A123'],
            'NIM panjang' => ['J0404241017ABC'],
        ];
    }

    /**
     * Feature: iotrack-improvements, Property 10: QR and Manual Input Equivalence
     * *For any* NIM value, submitting the Tap In form should produce identical results
     * whether the NIM was entered manually or via QR scan.
     * **Validates: Requirements 4.5**
     *
     * @dataProvider qrManualEquivalenceProvider
     */
    public function test_qr_and_manual_input_equivalence(
        string $nim,
        string $purpose,
        bool $withBorrowing
    ): void {
        // Setup: Buat mahasiswa dan item
        $student = $this->createStudent($nim, 'Student ' . $nim);
        $item = $this->createItem('Test Item', 10);

        // Siapkan data form
        $formData = [
            'visitor_id' => $nim,
            'purpose' => $purpose,
        ];

        if ($withBorrowing && $purpose === 'pinjam') {
            $formData['item_id'] = $item->id;
            $formData['quantity'] = 2;
        }

        // Act: Submit form (simulasi input manual atau QR - keduanya sama)
        $response = $this->post(route('visit.store'), $formData);

        // Assert: Visit dibuat dengan data yang benar
        $this->assertDatabaseHas('visits', [
            'visitor_id' => $nim,
            'visitor_name' => 'Student ' . $nim,
            'purpose' => $purpose,
        ]);

        // Jika ada peminjaman, verifikasi borrowing dibuat
        if ($withBorrowing && $purpose === 'pinjam') {
            $visit = Visit::where('visitor_id', $nim)->latest()->first();
            $this->assertDatabaseHas('borrowings', [
                'visit_id' => $visit->id,
                'item_id' => $item->id,
                'quantity' => 2,
                'status' => 'dipinjam',
            ]);
        }
    }

    public static function qrManualEquivalenceProvider(): array
    {
        return [
            'belajar tanpa pinjam' => ['J0404241001', 'belajar', false],
            'pinjam dengan item' => ['J0404241002', 'pinjam', true],
            'NIM numerik belajar' => ['12345678901', 'belajar', false],
            'NIM numerik pinjam' => ['12345678902', 'pinjam', true],
        ];
    }

    /**
     * Test tambahan: Verifikasi bahwa form submission dengan NIM dari QR
     * menghasilkan redirect yang sama dengan input manual.
     */
    public function test_form_submission_redirects_correctly_regardless_of_input_method(): void
    {
        $student = $this->createStudent('QR123456', 'QR Test Student');

        // Submit form (simulasi input dari QR atau manual)
        $response = $this->post(route('visit.store'), [
            'visitor_id' => 'QR123456',
            'purpose' => 'belajar',
        ]);

        // Assert: Redirect ke halaman welcome
        $visit = Visit::where('visitor_id', 'QR123456')->first();
        $response->assertRedirect(route('visit.welcome', $visit->id));
    }
}
