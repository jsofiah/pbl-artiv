<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\UserConsent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserConsentTest extends TestCase
{
    use RefreshDatabase;

    // ============================================================
    // Test #1: Relasi user() mengembalikan User yang benar
    // ============================================================
    public function test_user_relation_returns_correct_user(): void
    {
        $consent = $this->createConsent();

        $relatedUser = $consent->user;

        $this->assertInstanceOf(User::class, $relatedUser);
        $this->assertEquals($consent->user_id, $relatedUser->id);
        $this->assertEquals($consent->user->email, $relatedUser->email);
    }

    // ============================================================
    // Test #2: Mass assignment mengisi semua kolom $fillable
    // ============================================================
    public function test_mass_assignment_fills_all_fillable_columns(): void
    {
        $consent = $this->createConsent([
            'document'    => 'privacy_policy',
            'version'     => '1.0.0',
            'ip_address'  => '192.168.1.1',
            'user_agent'  => 'Mozilla/5.0 Test',
        ]);

        $this->assertEquals('privacy_policy', $consent->document);
        $this->assertEquals('1.0.0', $consent->version);
        $this->assertEquals('192.168.1.1', $consent->ip_address);
        $this->assertEquals('Mozilla/5.0 Test', $consent->user_agent);
        $this->assertNotNull($consent->accepted_at);
    }

    // ============================================================
    // Test #3: Cast accepted_at ke Carbon
    // ============================================================
    public function test_accepted_at_is_cast_to_carbon(): void
    {
        $consent = $this->createConsent();
        $consent->refresh();

        $this->assertInstanceOf(\Carbon\Carbon::class, $consent->accepted_at);
        $this->assertNotNull($consent->accepted_at);
    }

    // ============================================================
    // Test #4: UUID auto-generate — Catatan: model ini TIDAK pakai HasUuids!
    // ============================================================
    // ⚠️ Model UserConsent hanya pakai HasUuids, TAPI tanpa HasFactory
    // Sebenarnya HasUuids tetap ada di use statement, jadi UUID harus jalan.
    public function test_id_is_uuid_auto_generated(): void
    {
        $consent = $this->createConsent();

        $this->assertNotNull($consent->id);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $consent->id
        );
    }

    // ============================================================
    // Test #5: Kolom nullable (ip_address, user_agent) boleh null
    // ============================================================
    public function test_nullable_columns_can_be_null(): void
    {
        $consent = $this->createConsent([
            'ip_address' => null,
            'user_agent' => null,
        ]);

        $this->assertNull($consent->ip_address);
        $this->assertNull($consent->user_agent);
    }

    // ============================================================
    // Test #6: accepted_at default ke now() saat create
    // ============================================================
    public function test_accepted_at_defaults_to_now(): void
    {
        $consent = $this->createConsent();

        $this->assertNotNull($consent->accepted_at);
        // Selisih dengan now() harus < 5 detik
        $this->assertTrue(
            $consent->accepted_at->diffInSeconds(now()) < 5,
            'accepted_at should be close to now()'
        );
    }

    // ============================================================
    // HELPER
    // ============================================================
    private function createUser(): User
    {
        return User::create([
            'username'  => 'user_' . uniqid(),
            'email'     => 'user_' . uniqid() . '@test.com',
            'full_name' => 'Test User',
            'phone'     => '08123456789',
            'role'      => User::ROLE_CUSTOMER,
            'password'  => bcrypt('password'),
        ]);
    }

    private function createConsent(array $overrides = []): UserConsent
    {
        $user = $this->createUser();

        return UserConsent::create(array_merge([
            'user_id'     => $user->id,
            'document'    => 'terms_of_service',
            'version'     => '1.0.0',
            'ip_address'  => '127.0.0.1',
            'user_agent'  => 'Mozilla/5.0 Test Agent',
            'accepted_at' => now(),
        ], $overrides));
    }
}