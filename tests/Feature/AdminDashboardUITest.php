<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Visit;
use App\Models\Item;
use App\Models\Borrowing;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Test untuk komponen UI Admin Dashboard.
 * Mencakup sidebar navigation, layout, dan struktur dashboard.
 */
class AdminDashboardUITest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Buat admin user untuk testing
        $this->admin = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
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
            'description' => 'Test item description',
            'image' => 'items/test.jpg',
            'total_stock' => $stock,
            'current_stock' => $stock,
        ]);
    }


    // ========================================
    // SIDEBAR NAVIGATION TESTS
    // ========================================

    /**
     * Test: Sidebar menampilkan semua menu items yang diperlukan.
     * Validates: Requirements 8.1, 8.2
     */
    public function test_sidebar_displays_all_menu_items(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        
        // Verifikasi semua menu items ada di sidebar
        $response->assertSee('Menu Utama');
        $response->assertSee('Dashboard');
        $response->assertSee('Kunjungan');
        $response->assertSee('Inventaris');
        $response->assertSee('Peminjaman');
        $response->assertSee('Laporan');
    }

    /**
     * Test: Menu item aktif di-highlight dengan benar pada halaman Dashboard.
     * Validates: Requirements 8.5
     */
    public function test_active_menu_item_highlighted_on_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        
        // Verifikasi Dashboard menu memiliki class 'active'
        $response->assertSee('sidebar-menu-link active', false);
    }

    /**
     * Test: Menu item aktif di-highlight dengan benar pada halaman Inventaris.
     * Validates: Requirements 8.5
     */
    public function test_active_menu_item_highlighted_on_items_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('items.index'));

        $response->assertStatus(200);
        
        // Verifikasi halaman items memiliki menu yang aktif
        $response->assertSee('sidebar-menu-link active', false);
    }

    /**
     * Test: Sidebar memiliki tombol logout.
     * Validates: Requirements 8.1
     */
    public function test_sidebar_has_logout_button(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Keluar');
        $response->assertSee('sidebar-logout-btn', false);
    }

    /**
     * Test: Sidebar memiliki toggle button untuk mobile.
     * Validates: Requirements 8.4
     */
    public function test_sidebar_has_mobile_toggle(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        
        // Verifikasi toggle button ada
        $response->assertSee('sidebarToggle', false);
        $response->assertSee('sidebarClose', false);
        $response->assertSee('sidebarOverlay', false);
    }


    // ========================================
    // DASHBOARD LAYOUT TESTS
    // ========================================

    /**
     * Test: Dashboard menampilkan metric cards di bagian atas.
     * Validates: Requirements 9.1
     */
    public function test_dashboard_displays_metric_cards(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        
        // Verifikasi metric cards ada
        $response->assertSee('metrics-section', false);
        $response->assertSee('Sedang Dipinjam');
        $response->assertSee('Pengunjung Hari Ini');
    }

    /**
     * Test: Dashboard menampilkan section grafik analitik.
     * Validates: Requirements 9.2
     */
    public function test_dashboard_displays_charts_section(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        
        // Verifikasi charts section ada
        $response->assertSee('charts-section', false);
        $response->assertSee('Barang Paling Sering Dipinjam');
        $response->assertSee('Jumlah Pengunjung Harian');
        $response->assertSee('Distribusi Tujuan Kunjungan');
    }

    /**
     * Test: Dashboard menampilkan section tabel.
     * Validates: Requirements 9.3
     */
    public function test_dashboard_displays_tables_section(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        
        // Verifikasi tables section ada
        $response->assertSee('tables-section', false);
        $response->assertSee('borrowings-section', false);
        $response->assertSee('visits-section', false);
        $response->assertSee('Barang Belum Dikembalikan');
        $response->assertSee('Riwayat Kunjungan Hari Ini');
    }

    /**
     * Test: Dashboard layout menggunakan struktur yang benar.
     * Validates: Requirements 9.5
     */
    public function test_dashboard_has_correct_layout_structure(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        
        // Verifikasi struktur layout
        $response->assertSee('admin-sidebar', false);
        $response->assertSee('admin-main', false);
        $response->assertSee('admin-topbar', false);
        $response->assertSee('admin-content', false);
    }

    /**
     * Test: Dashboard menampilkan tanggal hari ini.
     * Validates: Requirements 9.1
     */
    public function test_dashboard_displays_current_date(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        
        // Verifikasi tanggal ditampilkan
        $response->assertSee('Tanggal Hari Ini');
    }


    // ========================================
    // ITEMS PAGE LAYOUT TESTS
    // ========================================

    /**
     * Test: Halaman items menggunakan layout admin yang sama.
     * Validates: Requirements 8.1
     */
    public function test_items_page_uses_admin_layout(): void
    {
        $response = $this->actingAs($this->admin)->get(route('items.index'));

        $response->assertStatus(200);
        
        // Verifikasi menggunakan layout yang sama
        $response->assertSee('admin-sidebar', false);
        $response->assertSee('admin-main', false);
    }

    /**
     * Test: Halaman items menampilkan tombol tambah barang.
     * Validates: Requirements 8.2
     */
    public function test_items_page_has_add_button(): void
    {
        $response = $this->actingAs($this->admin)->get(route('items.index'));

        $response->assertStatus(200);
        $response->assertSee('Tambah Barang Baru');
    }


    // ========================================
    // LOCALIZATION TESTS
    // ========================================

    /**
     * Test: Semua teks UI di dashboard dalam Bahasa Indonesia.
     * Validates: Requirements 10.3
     */
    public function test_dashboard_ui_text_in_bahasa_indonesia(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        
        // Verifikasi teks dalam Bahasa Indonesia
        $response->assertSee('Halo, Admin!');
        $response->assertSee('Berikut adalah ringkasan aktivitas Lab IoT hari ini');
        $response->assertSee('Sedang Dipinjam');
        $response->assertSee('Pengunjung Hari Ini');
        $response->assertSee('Barang Belum Dikembalikan');
        $response->assertSee('Riwayat Kunjungan Hari Ini');
        $response->assertSee('Ekspor CSV');
    }

    /**
     * Test: Semua teks UI di halaman items dalam Bahasa Indonesia.
     * Validates: Requirements 10.3
     */
    public function test_items_page_ui_text_in_bahasa_indonesia(): void
    {
        $response = $this->actingAs($this->admin)->get(route('items.index'));

        $response->assertStatus(200);
        
        // Verifikasi teks dalam Bahasa Indonesia
        $response->assertSee('Inventaris Lab IoT');
        $response->assertSee('Daftar Alat');
        $response->assertSee('Komponen');
        $response->assertSee('Tambah Barang Baru');
    }


    // ========================================
    // NAVIGATION TESTS
    // ========================================

    /**
     * Test: Navigasi dari sidebar ke dashboard berfungsi.
     * Validates: Requirements 8.3
     */
    public function test_sidebar_navigation_to_dashboard(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        
        // Verifikasi link ke dashboard ada
        $response->assertSee(route('admin.dashboard'));
    }

    /**
     * Test: Navigasi dari sidebar ke items berfungsi.
     * Validates: Requirements 8.3
     */
    public function test_sidebar_navigation_to_items(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        
        // Verifikasi link ke items ada
        $response->assertSee(route('items.index'));
    }

    /**
     * Test: Navigasi dari sidebar ke export berfungsi.
     * Validates: Requirements 8.3
     */
    public function test_sidebar_navigation_to_export(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        
        // Verifikasi link ke export ada
        $response->assertSee(route('admin.visits.export'));
    }


    // ========================================
    // RESPONSIVE BEHAVIOR TESTS
    // ========================================

    /**
     * Test: Dashboard memiliki class responsive untuk mobile.
     * Validates: Requirements 8.4, 9.4
     */
    public function test_dashboard_has_responsive_classes(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        
        // Verifikasi class responsive Bootstrap ada
        $response->assertSee('col-md-', false);
        $response->assertSee('col-lg-', false);
        $response->assertSee('d-none d-md-block', false);
        $response->assertSee('d-lg-none', false);
    }
}
