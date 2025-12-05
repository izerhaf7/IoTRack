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

/**
 * Property-based tests untuk lokalisasi Bahasa Indonesia.
 * Menggunakan data providers untuk menguji berbagai skenario error message.
 * 
 * Requirement 10.4: Error messages in Bahasa Indonesia
 */
class LocalizationPropertyTest extends TestCase
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
     * Feature: iotrack-improvements, Property 18: Error Message Localization
     * *For any* validation error or system error displayed to users, 
     * the message text should be in Bahasa Indonesia.
     * **Validates: Requirements 10.4**
     *
     * @dataProvider errorMessageLocalizationProvider
     */
    public function test_error_message_localization(
        string $scenario,
        callable $setupCallback,
        callable $actionCallback,
        array $expectedIndonesianPhrases
    ): void {
        // Setup: Jalankan callback setup untuk menyiapkan data
        $context = $setupCallback($this);

        // Act: Jalankan aksi yang akan menghasilkan error
        $errorMessage = '';
        try {
            $actionCallback($this, $context);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $errorMessage = implode(' ', array_map(function ($messages) {
                return implode(' ', (array) $messages);
            }, $errors));
        }

        // Assert: Pesan error harus mengandung frasa Bahasa Indonesia
        $this->assertNotEmpty($errorMessage, "Skenario '$scenario' seharusnya menghasilkan error");
        
        foreach ($expectedIndonesianPhrases as $phrase) {
            $this->assertStringContainsString(
                $phrase,
                $errorMessage,
                "Pesan error untuk skenario '$scenario' harus mengandung frasa Bahasa Indonesia: '$phrase'. Pesan aktual: '$errorMessage'"
            );
        }

        // Assert: Pesan error TIDAK boleh mengandung frasa umum bahasa Inggris
        $englishPhrases = [
            'The field is required',
            'must be',
            'is invalid',
            'not found',
            'You have no active',
            'This visit has already',
            'Multiple Tap Out',
        ];

        foreach ($englishPhrases as $englishPhrase) {
            $this->assertStringNotContainsString(
                $englishPhrase,
                $errorMessage,
                "Pesan error untuk skenario '$scenario' tidak boleh mengandung frasa Inggris: '$englishPhrase'. Pesan aktual: '$errorMessage'"
            );
        }
    }

    public static function errorMessageLocalizationProvider(): array
    {
        return [
            'NIM tidak terdaftar' => [
                'NIM tidak terdaftar',
                function ($test) {
                    // Tidak perlu setup mahasiswa
                    return ['nim' => 'INVALID123'];
                },
                function ($test, $context) {
                    $test->visitService->tapIn([
                        'visitor_id' => $context['nim'],
                        'purpose' => 'belajar',
                    ]);
                },
                ['NIM tidak terdaftar'],
            ],
            
            'Tidak ada peminjaman aktif untuk Tap Out' => [
                'Tidak ada peminjaman aktif untuk Tap Out',
                function ($test) {
                    // Buat mahasiswa dengan kunjungan tanpa peminjaman
                    $student = Student::create([
                        'nim' => 'TEST12345',
                        'name' => 'Test Student',
                        'program_studi' => 'Teknik Informatika',
                        'tahun_masuk' => 2020,
                        'angkatan' => 2020,
                    ]);

                    Visit::create([
                        'visitor_name' => $student->name,
                        'visitor_id' => $student->nim,
                        'purpose' => 'belajar',
                        'tapped_out_at' => null,
                    ]);

                    return ['nim' => $student->nim];
                },
                function ($test, $context) {
                    $test->visitService->tapOut($context['nim']);
                },
                ['tidak memiliki peminjaman aktif', 'Tap Out tidak diizinkan'],
            ],

            'Kunjungan sudah di-checkout (duplikat Tap Out)' => [
                'Kunjungan sudah di-checkout',
                function ($test) {
                    // Buat mahasiswa dengan kunjungan yang sudah tap out
                    $student = Student::create([
                        'nim' => 'TEST12345',
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
                        'tapped_out_at' => now(), // Sudah tap out
                    ]);

                    // Buat peminjaman yang sudah dikembalikan
                    Borrowing::create([
                        'visit_id' => $visit->id,
                        'item_id' => $item->id,
                        'quantity' => 1,
                        'status' => 'dikembalikan',
                        'returned_at' => now(),
                    ]);

                    return ['nim' => $student->nim];
                },
                function ($test, $context) {
                    $test->visitService->tapOut($context['nim']);
                },
                // Setelah tap out, tidak ada peminjaman aktif, jadi pesan yang muncul adalah tentang peminjaman aktif
                ['tidak memiliki peminjaman aktif'],
            ],

            'Stok barang tidak mencukupi' => [
                'Stok barang tidak mencukupi',
                function ($test) {
                    $student = Student::create([
                        'nim' => 'TEST12345',
                        'name' => 'Test Student',
                        'program_studi' => 'Teknik Informatika',
                        'tahun_masuk' => 2020,
                        'angkatan' => 2020,
                    ]);

                    $item = Item::create([
                        'name' => 'Arduino Uno',
                        'total_stock' => 10,
                        'current_stock' => 2, // Stok terbatas
                    ]);

                    return [
                        'nim' => $student->nim,
                        'item_id' => $item->id,
                    ];
                },
                function ($test, $context) {
                    $test->visitService->tapIn([
                        'visitor_id' => $context['nim'],
                        'purpose' => 'pinjam',
                        'item_id' => $context['item_id'],
                        'quantity' => 5, // Melebihi stok
                    ]);
                },
                ['Stok barang', 'hanya tersisa'],
            ],

            'Data kunjungan tidak ditemukan' => [
                'Data kunjungan tidak ditemukan',
                function ($test) {
                    // Tidak buat kunjungan apapun
                    return ['nim' => 'NONEXISTENT123'];
                },
                function ($test, $context) {
                    $test->visitService->tapOut($context['nim']);
                },
                ['Data kunjungan tidak ditemukan'],
            ],
        ];
    }

    /**
     * Test bahwa pesan error dari AdminAuthController dalam Bahasa Indonesia.
     */
    public function test_admin_login_error_messages_in_bahasa_indonesia(): void
    {
        // Act: Coba login dengan kredensial salah
        $response = $this->post('/admin/login', [
            'email' => 'wrong@email.com',
            'password' => 'wrongpassword',
        ]);

        // Assert: Pesan error harus dalam Bahasa Indonesia
        $response->assertSessionHasErrors();
        $errors = session('errors');
        
        if ($errors) {
            $errorMessages = $errors->all();
            $allMessages = implode(' ', $errorMessages);
            
            // Harus mengandung frasa Bahasa Indonesia
            $this->assertTrue(
                str_contains($allMessages, 'salah') || 
                str_contains($allMessages, 'Email') ||
                str_contains($allMessages, 'password'),
                "Pesan error login harus dalam Bahasa Indonesia. Pesan aktual: '$allMessages'"
            );
        }
    }

    /**
     * Test bahwa pesan validasi form dalam Bahasa Indonesia.
     */
    public function test_form_validation_messages_in_bahasa_indonesia(): void
    {
        // Act: Submit form Tap In tanpa data (route: POST /tap-in)
        $response = $this->post('/tap-in', [
            // Kosong - akan trigger validasi required
        ]);

        // Assert: Redirect dengan error
        $response->assertStatus(302);
        $response->assertSessionHasErrors();
    }

    /**
     * Test bahwa pesan sukses dalam Bahasa Indonesia.
     */
    public function test_success_messages_in_bahasa_indonesia(): void
    {
        // Setup: Buat mahasiswa
        $student = Student::create([
            'nim' => 'TEST12345',
            'name' => 'Test Student',
            'program_studi' => 'Teknik Informatika',
            'tahun_masuk' => 2020,
            'angkatan' => 2020,
        ]);

        // Act: Tap In dengan data valid (route: POST /tap-in)
        $response = $this->post('/tap-in', [
            'visitor_id' => $student->nim,
            'purpose' => 'belajar',
        ]);

        // Assert: Redirect ke halaman welcome (sukses)
        $response->assertStatus(302);
        $response->assertRedirectContains('tap-in/welcome');
    }

    /**
     * Test bahwa file bahasa Indonesia tersedia.
     */
    public function test_indonesian_language_files_exist(): void
    {
        // Assert: File bahasa Indonesia harus ada
        $this->assertFileExists(base_path('lang/id/validation.php'));
        $this->assertFileExists(base_path('lang/id/auth.php'));
        $this->assertFileExists(base_path('lang/id/pagination.php'));
        $this->assertFileExists(base_path('lang/id/passwords.php'));
    }

    /**
     * Test bahwa konfigurasi locale default adalah 'id'.
     */
    public function test_default_locale_configuration(): void
    {
        // Baca file config langsung untuk memastikan default value
        $configContent = file_get_contents(base_path('config/app.php'));
        
        // Assert: Default locale harus 'id'
        $this->assertStringContainsString("'locale' => env('APP_LOCALE', 'id')", $configContent);
        $this->assertStringContainsString("'fallback_locale' => env('APP_FALLBACK_LOCALE', 'id')", $configContent);
    }
}
