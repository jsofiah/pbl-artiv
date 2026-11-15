<?php

namespace Tests\Unit\Models;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    // ============================================================
    // Test #1: Relasi user() mengembalikan User yang benar
    // ============================================================
    public function test_user_relation_returns_correct_user(): void
    {
        // Arrange: bikin user + activity log
        $user = $this->createUser();
        $log = ActivityLog::create([
            'user_id'     => $user->id,
            'action'      => 'login',
            'target_type' => null,
            'target_id'   => null,
            'description' => 'User login ke aplikasi',
        ]);

        // Act
        $relatedUser = $log->user;

        // Assert
        $this->assertInstanceOf(User::class, $relatedUser);
        $this->assertEquals($user->id, $relatedUser->id);
        $this->assertEquals($user->email, $relatedUser->email);
    }

    // ============================================================
    // Test #2: Mass assignment mengisi semua kolom $fillable
    // ============================================================
    public function test_mass_assignment_fills_all_fillable_columns(): void
    {
        // Arrange
        $user = $this->createUser();

        // Act: create via mass assignment (array)
        $log = ActivityLog::create([
            'user_id'     => $user->id,
            'action'      => 'create_order',
            'target_type' => 'App\Models\Order',
            'target_id'   => '123e4567-e89b-12d3-a456-426614174000',
            'description' => 'Customer membuat order baru',
        ]);

        // Assert: semua kolom terisi
        $this->assertEquals($user->id, $log->user_id);
        $this->assertEquals('create_order', $log->action);
        $this->assertEquals('App\Models\Order', $log->target_type);
        $this->assertEquals('123e4567-e89b-12d3-a456-426614174000', $log->target_id);
        $this->assertEquals('Customer membuat order baru', $log->description);
    }

    // ============================================================
    // Test #3: UUID auto-generate saat create
    // ============================================================
    public function test_id_is_uuid_auto_generated(): void
    {
        // Arrange
        $user = $this->createUser();

        // Act
        $log = ActivityLog::create([
            'user_id' => $user->id,
            'action'  => 'test',
        ]);

        // Assert: ID adalah UUID (format 8-4-4-4-12)
        $this->assertNotNull($log->id);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $log->id
        );
    }

    // ============================================================
    // Test #4: Kolom nullable boleh null
    // ============================================================
    public function test_nullable_columns_can_be_null(): void
    {
        // Arrange
        $user = $this->createUser();

        // Act: create tanpa target_type, target_id, description
        $log = ActivityLog::create([
            'user_id' => $user->id,
            'action'  => 'logout',
        ]);

        // Assert
        $this->assertNull($log->target_type);
        $this->assertNull($log->target_id);
        $this->assertNull($log->description);
    }

    // ============================================================
    // HELPER: bikin user dengan kolom wajib
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
}