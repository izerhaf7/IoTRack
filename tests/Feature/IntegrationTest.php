<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Visit;
use App\Models\Item;
use App\Models\Borrowing;
use App\Models\Student;
use App\Models\User;
use App\Services\VisitService;
use App\Services\BorrowingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Integration tests untuk alur lengkap aplikasi IoTrack.
 * Menguji end-to-end flow dari berbagai fitur utama.
 */
class IntegrationTest extends TestCase
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

    /**
     * Helper untuk membuat admin user.
     */
    protected function createAdmin(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
    }


    // ========================================
    // INTEGRATION TEST: TAP IN → TAP OUT FLOW
    // ========================================

    /**
     * Test alur lengkap Tap In → Tap Out untuk kunjungan belajar.
     */
    public function test_complete_tap_in_tap_out_flow_for_study_visit(): void
    {
        // Setup
        $student = $this->createStudent('STUDY001', 'Study Student');

        // Step 1: Tap In untuk belajar
        $response = $this->post(route('visit.store'), [
            'visitor_id' => 'STUDY001',
            'purpose' => 'belajar',
        ]);

        // Verifikasi visit dibuat
        $visit = Visit::where('visitor_id', 'STUDY001')->first();
        $this->assertNotNull($visit);
        $this->assertEquals('belajar', $visit->purpose);
        $this->assertNull($visit->tapped_out_at);

        // Verifikasi redirect ke halaman welcome
        $response->assertRedirect(route('visit.welcome', $visit->id));

        // Step 2: Coba Tap Out (harus gagal karena tidak ada peminjaman)
        $tapOutResponse = $this->post(route('visit.tap-out-process'), [
            'visitor_id' => 'STUDY001',
        ]);

        // Verifikasi error ditampilkan
        $tapOutResponse->assertSessionHas('error');
        
        // Verifikasi visit tidak berubah
        $visit->refresh();
        $this->assertNull($visit->tapped_out_at);
    }

    /**
     * Test alur lengkap Tap In → Tap Out untuk kunjungan meminjam.
     */
    public function test_complete_tap_in_tap_out_flow_for_borrowing_visit(): void
    {
        // Setup
        $student = $this->createStudent('BORROW001', 'Borrowing Student');
        $item = $this->createItem('Arduino Uno', 10);

        // Step 1: Tap In untuk meminjam
        $response = $this->post(route('visit.store'), [
            'visitor_id' => 'BORROW001',
            'purpose' => 'pinjam',
            'item_id' => $item->id,
            'quantity' => 2,
        ]);

        // Verifikasi visit dibuat
        $visit = Visit::where('visitor_id', 'BORROW001')->first();
        $this->assertNotNull($visit);
        $this->assertEquals('pinjam', $visit->purpose);
        $this->assertNull($visit->tapped_out_at);

        // Verifikasi borrowing dibuat
        $this->assertDatabaseHas('borrowings', [
            'visit_id' => $visit->id,
            'item_id' => $item->id,
            'quantity' => 2,
            'status' => 'dipinjam',
        ]);

        // Verifikasi stok berkurang
        $this->assertEquals(8, $item->fresh()->current_stock);

        // Step 2: Tap Out
        $tapOutResponse = $this->post(route('visit.tap-out-process'), [
            'visitor_id' => 'BORROW001',
        ]);

        // Verifikasi redirect ke halaman goodbye
        $tapOutResponse->assertRedirect(route('visit.goodbye', ['visitor_id' => 'BORROW001']));

        // Verifikasi visit di-tap out
        $visit->refresh();
        $this->assertNotNull($visit->tapped_out_at);

        // Verifikasi borrowing dikembalikan
        $this->assertDatabaseHas('borrowings', [
            'visit_id' => $visit->id,
            'status' => 'dikembalikan',
        ]);

        // Verifikasi stok dikembalikan
        $this->assertEquals(10, $item->fresh()->current_stock);
    }

    /**
     * Test bahwa Tap Out kedua kali ditolak.
     */
    public function test_duplicate_tap_out_is_rejected(): void
    {
        // Setup
        $student = $this->createStudent('DUP001', 'Duplicate Student');
        $item = $this->createItem('Test Item', 10);

        // Tap In
        $this->post(route('visit.store'), [
            'visitor_id' => 'DUP001',
            'purpose' => 'pinjam',
            'item_id' => $item->id,
            'quantity' => 1,
        ]);

        // Tap Out pertama (berhasil)
        $this->post(route('visit.tap-out-process'), [
            'visitor_id' => 'DUP001',
        ]);

        // Verifikasi stok dikembalikan
        $this->assertEquals(10, $item->fresh()->current_stock);

        // Tap Out kedua (harus gagal)
        $response = $this->post(route('visit.tap-out-process'), [
            'visitor_id' => 'DUP001',
        ]);

        // Verifikasi error
        $response->assertSessionHas('error');

        // Verifikasi stok tidak berubah (tidak double return)
        $this->assertEquals(10, $item->fresh()->current_stock);
    }


    // ========================================
    // INTEGRATION TEST: ADMIN LOGIN → DASHBOARD → OPERATIONS
    // ========================================

    /**
     * Test alur admin login → dashboard → operasi.
     */
    public function test_admin_login_dashboard_operations_flow(): void
    {
        // Setup
        $admin = $this->createAdmin();
        $student = $this->createStudent('ADMIN001', 'Admin Test Student');
        $item = $this->createItem('Admin Test Item', 10);

        // Buat kunjungan dengan peminjaman
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

        $item->update(['current_stock' => 8]);

        // Step 1: Akses dashboard tanpa login (harus redirect ke login)
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));

        // Step 2: Login
        $loginResponse = $this->post(route('admin.login.process'), [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);
        $loginResponse->assertRedirect(route('admin.dashboard'));

        // Step 3: Akses dashboard setelah login
        $dashboardResponse = $this->actingAs($admin)->get(route('admin.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Admin Test Student');
        $dashboardResponse->assertSee('Admin Test Item');

        // Step 4: Return item dari dashboard
        $returnResponse = $this->actingAs($admin)->post(route('admin.return', $borrowing->id));
        $returnResponse->assertRedirect();
        $returnResponse->assertSessionHas('success');

        // Verifikasi borrowing dikembalikan
        $this->assertDatabaseHas('borrowings', [
            'id' => $borrowing->id,
            'status' => 'dikembalikan',
        ]);

        // Verifikasi stok dikembalikan
        $this->assertEquals(10, $item->fresh()->current_stock);
    }

    /**
     * Test admin dapat menghapus kunjungan tanpa peminjaman aktif.
     */
    public function test_admin_can_delete_visit_without_active_borrowings(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('DEL001', 'Delete Test Student');

        // Buat kunjungan tanpa peminjaman
        $visit = Visit::create([
            'visitor_name' => $student->name,
            'visitor_id' => $student->nim,
            'purpose' => 'belajar',
            'tapped_out_at' => null,
        ]);

        // Hapus kunjungan
        $response = $this->actingAs($admin)->delete(route('admin.visit.destroy', $visit->id));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verifikasi kunjungan dihapus
        $this->assertDatabaseMissing('visits', ['id' => $visit->id]);
    }

    /**
     * Test admin tidak dapat menghapus kunjungan dengan peminjaman aktif.
     */
    public function test_admin_cannot_delete_visit_with_active_borrowings(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('NODELETE001', 'No Delete Student');
        $item = $this->createItem('No Delete Item', 10);

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

        // Coba hapus kunjungan
        $response = $this->actingAs($admin)->delete(route('admin.visit.destroy', $visit->id));
        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Verifikasi kunjungan tidak dihapus
        $this->assertDatabaseHas('visits', ['id' => $visit->id]);
    }


    // ========================================
    // INTEGRATION TEST: EXCEL EXPORT FLOW
    // ========================================

    /**
     * Test alur ekspor Excel dari dashboard.
     */
    public function test_excel_export_download_flow(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('EXPORT001', 'Export Test Student');
        $item = $this->createItem('Export Test Item', 10);

        // Buat kunjungan hari ini
        $visit = Visit::create([
            'visitor_name' => $student->name,
            'visitor_id' => $student->nim,
            'purpose' => 'pinjam',
            'tapped_out_at' => null,
        ]);

        Borrowing::create([
            'visit_id' => $visit->id,
            'item_id' => $item->id,
            'quantity' => 3,
            'status' => 'dipinjam',
        ]);

        // Akses export endpoint
        $response = $this->actingAs($admin)->get(route('admin.visits.export'));

        // Verifikasi response adalah file download
        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        
        // Verifikasi filename mengandung tanggal hari ini
        $expectedFilename = 'visits_' . now()->format('Y-m-d') . '.csv';
        $response->assertHeader('Content-Disposition', 'attachment; filename="' . $expectedFilename . '"');

        // Verifikasi konten CSV
        $content = $response->getContent();
        $this->assertStringContainsString('Tanggal', $content);
        $this->assertStringContainsString('Nama Pengunjung', $content);
        $this->assertStringContainsString('Export Test Student', $content);
        $this->assertStringContainsString('Export Test Item', $content);
        $this->assertStringContainsString('Qty: 3', $content);
    }

    /**
     * Test ekspor dengan kunjungan tanpa peminjaman menampilkan dash.
     */
    public function test_excel_export_shows_dash_for_no_borrowings(): void
    {
        $admin = $this->createAdmin();
        $student = $this->createStudent('NODASH001', 'No Borrowing Student');

        // Buat kunjungan tanpa peminjaman
        Visit::create([
            'visitor_name' => $student->name,
            'visitor_id' => $student->nim,
            'purpose' => 'belajar',
            'tapped_out_at' => null,
        ]);

        // Akses export endpoint
        $response = $this->actingAs($admin)->get(route('admin.visits.export'));

        // Verifikasi konten CSV mengandung dash
        $content = $response->getContent();
        $this->assertStringContainsString('No Borrowing Student', $content);
        $this->assertStringContainsString('Belajar', $content);
        // Verifikasi ada dash di kolom detail peminjaman
        $lines = explode("\n", $content);
        $dataLine = $lines[1] ?? '';
        $this->assertStringContainsString('-', $dataLine);
    }


    // ========================================
    // INTEGRATION TEST: QR SCAN → FORM SUBMIT
    // ========================================

    /**
     * Test alur QR scan → form submit (simulasi client-side).
     * Karena QR scanning adalah fitur JavaScript, kita test bahwa
     * form submission dengan NIM dari QR bekerja dengan benar.
     */
    public function test_qr_scan_form_submit_flow(): void
    {
        $student = $this->createStudent('QR123456', 'QR Test Student');
        $item = $this->createItem('QR Test Item', 10);

        // Simulasi: NIM di-scan dari QR code dan diisi ke form
        // Kemudian form di-submit
        $response = $this->post(route('visit.store'), [
            'visitor_id' => 'QR123456', // Nilai dari QR scan
            'purpose' => 'pinjam',
            'item_id' => $item->id,
            'quantity' => 1,
        ]);

        // Verifikasi visit dibuat dengan benar
        $visit = Visit::where('visitor_id', 'QR123456')->first();
        $this->assertNotNull($visit);
        $this->assertEquals('QR Test Student', $visit->visitor_name);

        // Verifikasi redirect ke welcome page
        $response->assertRedirect(route('visit.welcome', $visit->id));
    }

    /**
     * Test bahwa halaman Tap In memiliki elemen QR scanner.
     */
    public function test_tap_in_page_has_qr_scanner_elements(): void
    {
        $response = $this->get(route('visit.create'));

        $response->assertStatus(200);
        
        // Verifikasi elemen QR scanner ada
        $response->assertSee('qrScanBtn', false);
        $response->assertSee('qrScannerContainer', false);
        $response->assertSee('html5-qrcode', false);
    }
}
