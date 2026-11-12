<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Mockery;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

#[RunTestsInSeparateProcesses]
class LogoutTest extends TestCase
{
    // JANGAN pakai RefreshDatabase karena kita tidak mau sentuh DB
    // use RefreshDatabase; 

    protected function setUp(): void
    {
        parent::setUp();
        
        // Paksa session pakai array (tidak butuh tabel sessions di DB)
        config(['session.driver' => 'array']);
    }

    /** @test TC-LO-002 */
    public function test_session_pengguna_terhapus_setelah_logout(): void
    {
        // 1. Buat User Palsu (Mocking)
        $user = new User([
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'username' => 'mock_customer',
            'email' => 'mock@test.com',
            'full_name' => 'Mock Customer',
            'role' => 'customer',
        ]);

        // 2. Paksa sistem menganggap user ini sudah login
        $this->actingAs($user);
        $this->assertAuthenticatedAs($user);

        // 3. Tembak route logout
        $response = $this->post('/logout');

        // 4. Pastikan session auth hilang
        $response->assertRedirect('/');
        $this->assertGuest(); // PASS jika user sudah tidak login
    }

    /** @test TC-LO-003 */
    public function test_tombol_kembali_tidak_bisa_membuka_halaman_setelah_logout(): void
    {
        $user = new User([
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'role' => 'customer',
        ]);

        $this->actingAs($user);
        $this->post('/logout');

        // Simulasi akses halaman protected setelah logout
        $response = $this->get('/customer/pesanan');

        // Harus dilempar ke login
        $response->assertRedirect('/login');
    }

        /** @test TC-LO-004 */
    public function test_pengguna_tidak_dapat_mengakses_dashboard_setelah_logout(): void
    {
        $user = new User([
            'id' => '123e4567-e89b-12d3-a456-426614174000',
            'role' => 'customer',
        ]);

        $this->actingAs($user);
        $this->post('/logout');

        // Coba akses halaman pesanan customer (halaman ini dilindungi middleware auth)
        $response = $this->get('/customer/pesanan');

        // Harus dilempar ke login
        $response->assertRedirect('/login');
    }
}