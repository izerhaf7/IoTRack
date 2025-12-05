<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Property-based tests untuk otentikasi admin.
 * Menggunakan data providers untuk menguji berbagai skenario redirect.
 */
class AuthenticationRedirectPropertyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Feature: iotrack-improvements, Property 15: Authentication Redirect Consistency
     * *For any* protected admin route, accessing it without authentication should redirect 
     * to "/admin/login" regardless of which specific admin page was requested.
     * **Validates: Requirements 11.3**
     *
     * @dataProvider protectedAdminRoutesProvider
     */
    public function test_authentication_redirect_consistency(
        string $method,
        string $route,
        string $description
    ): void {
        // Act: Akses route tanpa otentikasi
        $response = $this->$method($route);

        // Assert: Harus redirect ke /admin/login
        $response->assertRedirect('/admin/login');
    }

    /**
     * Data provider untuk semua protected admin routes.
     * Setiap route yang dilindungi middleware auth harus redirect ke /admin/login.
     */
    public static function protectedAdminRoutesProvider(): array
    {
        return [
            'admin menu' => ['get', '/admin/menu', 'Menu utama admin'],
            'admin dashboard' => ['get', '/admin/dashboard', 'Dashboard admin'],
            'items index' => ['get', '/admin/items', 'Daftar barang'],
            'items create' => ['get', '/admin/items/create', 'Form tambah barang'],
            'items store' => ['post', '/admin/items', 'Simpan barang baru'],
            'items show' => ['get', '/admin/items/1', 'Detail barang'],
            'items edit' => ['get', '/admin/items/1/edit', 'Form edit barang'],
            'items update' => ['put', '/admin/items/1', 'Update barang'],
            'items destroy' => ['delete', '/admin/items/1', 'Hapus barang'],
            'return item' => ['post', '/admin/return/1', 'Terima pengembalian barang'],
            'delete visit' => ['delete', '/admin/visit/delete/1', 'Hapus riwayat kunjungan'],
        ];
    }

    /**
     * Test tambahan: Authenticated user dapat mengakses admin routes.
     * Memastikan bahwa setelah login, user dapat mengakses halaman admin.
     */
    public function test_authenticated_user_can_access_admin_routes(): void
    {
        // Setup: Buat user admin
        $user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        // Act & Assert: User yang sudah login dapat mengakses admin menu
        $response = $this->actingAs($user)->get('/admin/menu');
        $response->assertStatus(200);

        // Act & Assert: User yang sudah login dapat mengakses admin dashboard
        $response = $this->actingAs($user)->get('/admin/dashboard');
        $response->assertStatus(200);
    }
}
