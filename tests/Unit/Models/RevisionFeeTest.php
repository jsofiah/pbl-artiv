<?php

namespace Tests\Unit\Models;

use App\Models\RevisionFee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevisionFeeTest extends TestCase
{
    use RefreshDatabase;

    // ============================================================
    // Test #1: Scope active() hanya return yang is_active = true
    // ============================================================
    public function test_scope_active_returns_only_active_fees(): void
    {
        $active = RevisionFee::create([
            'name'      => 'Revisi Biasa',
            'fee'       => 25000,
            'is_active' => true,
        ]);

        RevisionFee::create([
            'name'      => 'Revisi Premium (nonaktif)',
            'fee'       => 50000,
            'is_active' => false,
        ]);

        $activeFees = RevisionFee::active()->get();

        $this->assertCount(1, $activeFees);
        $this->assertEquals($active->id, $activeFees->first()->id);
        $this->assertTrue($activeFees->first()->is_active);
    }

    // ============================================================
    // Test #2: Scope active() return kosong kalau tidak ada yang aktif
    // ============================================================
    public function test_scope_active_returns_empty_when_no_active_fees(): void
    {
        RevisionFee::create([
            'name'      => 'Nonaktif 1',
            'fee'       => 25000,
            'is_active' => false,
        ]);
        RevisionFee::create([
            'name'      => 'Nonaktif 2',
            'fee'       => 50000,
            'is_active' => false,
        ]);

        $activeFees = RevisionFee::active()->get();

        $this->assertCount(0, $activeFees);
        $this->assertTrue($activeFees->isEmpty());
    }

    // ============================================================
    // Test #3: Cast fee ke decimal string
    // ============================================================
    public function test_fee_is_cast_to_decimal(): void
    {
        $fee = RevisionFee::create([
            'name'      => 'Revisi Test',
            'fee'       => 30000.75,
            'is_active' => true,
        ]);
        $fee->refresh();

        // decimal:2 → string "30000.75"
        $this->assertEquals('30000.75', $fee->fee);
    }

    // ============================================================
    // Test #4: Cast is_active ke boolean
    // ============================================================
    public function test_is_active_is_cast_to_boolean(): void
    {
        $fee = RevisionFee::create([
            'name'      => 'Revisi',
            'fee'       => 25000,
            'is_active' => 1, // int
        ]);
        $fee->refresh();

        $this->assertTrue($fee->is_active);
        $this->assertIsBool($fee->is_active);

        $fee2 = RevisionFee::create([
            'name'      => 'Revisi 2',
            'fee'       => 25000,
            'is_active' => 0,
        ]);
        $fee2->refresh();

        $this->assertFalse($fee2->is_active);
        $this->assertIsBool($fee2->is_active);
    }

    // ============================================================
    // Test #5: UUID auto-generate
    // ============================================================
    public function test_id_is_uuid_auto_generated(): void
    {
        $fee = RevisionFee::create([
            'name'      => 'Revisi Test',
            'fee'       => 25000,
            'is_active' => true,
        ]);

        $this->assertNotNull($fee->id);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $fee->id
        );
    }

    // ============================================================
    // Test #6: Mass assignment mengisi semua kolom $fillable
    // ============================================================
    public function test_mass_assignment_fills_all_fillable_columns(): void
    {
        $fee = RevisionFee::create([
            'name'      => 'Revisi Cepat',
            'fee'       => 45000,
            'is_active' => true,
        ]);

        $this->assertEquals('Revisi Cepat', $fee->name);
        $this->assertEquals(45000, $fee->fee);
        $this->assertTrue($fee->is_active);
    }
}