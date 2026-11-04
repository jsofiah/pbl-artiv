<?php

namespace Tests\Unit\Models;

use App\Models\OrderSplit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\TestCase;

class OrderSplitTest extends TestCase
{
    use RefreshDatabase;

    private function makeNormalSplit(array $overrides = []): OrderSplit
    {
        return new OrderSplit(array_merge([
            'order_id'          => '00000000-0000-0000-0000-000000000001',
            'designer_id'       => '00000000-0000-0000-0000-000000000002',
            'split_type'        => OrderSplit::TYPE_NORMAL,
            'commission_rate'   => OrderSplit::COMMISSION_NORMAL,
            'gross_amount'      => 100000,
            'commission_amount' => 10000,
            'designer_earning'  => 90000,
        ], $overrides));
    }

    private function makeReassignmentSplit(array $overrides = []): OrderSplit
    {
        return new OrderSplit(array_merge([
            'order_id'          => '00000000-0000-0000-0000-000000000001',
            'designer_id'       => '00000000-0000-0000-0000-000000000002',
            'split_type'        => OrderSplit::TYPE_REASSIGNMENT,
            'commission_rate'   => OrderSplit::COMMISSION_REASSIGNMENT,
            'gross_amount'      => 100000,
            'commission_amount' => 5000,
            'designer_earning'  => 95000,
        ], $overrides));
    }

    public function test_normal_split_has_10_percent_commission_and_90_percent_designer(): void
    {
        $split = $this->makeNormalSplit();

        $this->assertEquals(OrderSplit::TYPE_NORMAL, $split->split_type);
        $this->assertEquals(10.00, (float) $split->commission_rate);
        $this->assertEquals(90.0, $split->designerPercentage());
        $this->assertTrue($split->isNormal());
        $this->assertFalse($split->isReassignment());
    }

    public function test_reassignment_split_has_5_percent_commission_and_95_percent_designer(): void
    {
        $split = $this->makeReassignmentSplit();

        $this->assertEquals(OrderSplit::TYPE_REASSIGNMENT, $split->split_type);
        $this->assertEquals(5.00, (float) $split->commission_rate);
        $this->assertEquals(95.0, $split->designerPercentage());
        $this->assertTrue($split->isReassignment());
        $this->assertFalse($split->isNormal());
    }

    public function test_split_constants_have_expected_values(): void
    {
        $this->assertSame('normal', OrderSplit::TYPE_NORMAL);
        $this->assertSame('reassignment', OrderSplit::TYPE_REASSIGNMENT);
        
        $this->assertSame(10.00, OrderSplit::COMMISSION_NORMAL);
        $this->assertSame(5.00, OrderSplit::COMMISSION_REASSIGNMENT);
    }

    public function test_split_belongs_to_order_and_designer(): void
    {
        $split = $this->makeNormalSplit();

        $this->assertInstanceOf(BelongsTo::class, $split->order());
        $this->assertInstanceOf(BelongsTo::class, $split->designer());
    }

    public function test_split_uses_uuid_as_primary_key(): void
    {
        $split = $this->makeNormalSplit();
        $traits = class_uses_recursive($split);
        
        $this->assertTrue(in_array(\Illuminate\Database\Eloquent\Concerns\HasUuids::class, $traits));
    }

    public function test_split_amounts_are_cast_to_decimal_with_two_places(): void
    {
        $split = $this->makeNormalSplit();
        
        $this->assertSame('100000.00', (string) $split->gross_amount);
        $this->assertSame('10000.00', (string) $split->commission_amount);
        $this->assertSame('90000.00', (string) $split->designer_earning);
        $this->assertSame('10.00', (string) $split->commission_rate);
    }
}