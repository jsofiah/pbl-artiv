<?php

namespace Tests\Unit\Models;

use App\Models\DesignerStat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DesignerStatTest extends TestCase
{
    use RefreshDatabase;

    // ============================================================
    // Test #1: Status available → isAvailable() true
    // ============================================================
    public function test_is_available_true_for_available_status(): void
    {
        $stat = $this->createDesignerStat([
            'availability_status' => DesignerStat::STATUS_AVAILABLE,
        ]);

        $this->assertTrue($stat->isAvailable());
        $this->assertFalse($stat->isSuspended());
        $this->assertFalse($stat->isSuspendExpired());
    }

    // ============================================================
    // Test #2: Status unavailable → isAvailable() false
    // ============================================================
    public function test_is_available_false_for_unavailable_status(): void
    {
        $stat = $this->createDesignerStat([
            'availability_status' => DesignerStat::STATUS_UNAVAILABLE,
        ]);

        $this->assertFalse($stat->isAvailable());
        $this->assertFalse($stat->isSuspended());
    }

    // ============================================================
    // Test #3: Suspended dengan tanggal未来 → isSuspended() true, isAvailable() false
    // ============================================================
    public function test_suspended_with_future_date_returns_suspended_and_not_available(): void
    {
        $stat = $this->createDesignerStat([
            'availability_status' => DesignerStat::STATUS_SUSPENDED,
            'suspended_until'     => now()->addDays(3), // 3 hari lagi
        ]);

        $this->assertTrue($stat->isSuspended());
        $this->assertFalse($stat->isAvailable());
        $this->assertFalse($stat->isSuspendExpired());
    }

    // ============================================================
    // Test #4: Suspended dengan tanggal lampau → isSuspendExpired() true
    // ============================================================
    public function test_suspended_with_past_date_returns_expired(): void
    {
        $stat = $this->createDesignerStat([
            'availability_status' => DesignerStat::STATUS_SUSPENDED,
            'suspended_until'     => now()->subDays(1), // kemarin
        ]);

        $this->assertTrue($stat->isSuspendExpired());
        $this->assertFalse($stat->isSuspended());
        // isAvailable masih false karena status suspended (meski suspend expired)
        $this->assertFalse($stat->isAvailable());
    }

    // ============================================================
    // Test #5: available tapi suspended_until masih future → isAvailable() false
    // ============================================================
    public function test_available_but_still_suspended_until_future_returns_not_available(): void
    {
        // Ini edge case: admin set available, tapi masa suspend belum habis
        $stat = $this->createDesignerStat([
            'availability_status' => DesignerStat::STATUS_AVAILABLE,
            'suspended_until'     => now()->addDays(2),
        ]);

        $this->assertFalse($stat->isAvailable()); // Karena masih ada suspended_until future
        $this->assertFalse($stat->isSuspended()); // Status bukan suspended
    }

    // ============================================================
    // Test #6: Relasi designer() mengembalikan User yang benar
    // ============================================================
    public function test_designer_relation_returns_correct_user(): void
    {
        $stat = $this->createDesignerStat();

        $relatedDesigner = $stat->designer;

        $this->assertInstanceOf(User::class, $relatedDesigner);
        $this->assertEquals($stat->designer_id, $relatedDesigner->id);
        $this->assertEquals(User::ROLE_DESIGNER, $relatedDesigner->role);
    }

    // ============================================================
    // Test #7: Cast average_rating & total_earning ke decimal
    // ============================================================
    public function test_casts_decimal_columns(): void
    {
        $stat = $this->createDesignerStat([
            'average_rating' => 4.75,
            'total_earning'  => 1250000.50,
        ]);

        $stat->refresh();

        // decimal:2 → string "4.75" dan "1250000.50"
        $this->assertEquals('4.75', $stat->average_rating);
        $this->assertEquals('1250000.50', $stat->total_earning);
    }

    // ============================================================
    // HELPER: bikin User designer + DesignerStat
    // ============================================================
    private function createDesignerStat(array $overrides = []): DesignerStat
    {
        $designer = User::create([
            'username'  => 'des_' . uniqid(),
            'email'     => 'des_' . uniqid() . '@test.com',
            'full_name' => 'Test Designer',
            'phone'     => '08123456789',
            'role'      => User::ROLE_DESIGNER,
            'password'  => bcrypt('password'),
        ]);

        return DesignerStat::create(array_merge([
            'designer_id'         => $designer->id,
            'total_orders'        => 0,
            'completed_orders'    => 0,
            'average_rating'      => 0,
            'total_earning'       => 0,
            'daily_order_limit'   => DesignerStat::DEFAULT_DAILY_LIMIT,
            'max_active_orders'   => DesignerStat::DEFAULT_MAX_ACTIVE,
            'availability_status' => DesignerStat::STATUS_AVAILABLE,
            'late_count'          => 0,
            'total_late_count'    => 0,
            'suspend_count'       => 0,
            'last_suspended_at'   => null,
            'suspended_until'     => null,
        ], $overrides));
    }
}