<?php

namespace Tests\Unit\Observers;

use App\Models\DesignerStat;
use App\Models\Order;
use App\Models\OrderSplit;
use App\Models\Product;
use App\Models\ProductTier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderSplitObserverTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $role): User
    {
        return User::create([
            'username'  => $role . '_' . uniqid(),
            'email'     => $role . '_' . uniqid() . '@test.com',
            'full_name' => 'Test ' . ucfirst($role),
            'phone'     => '0812345678' . rand(10, 99),
            'role'      => $role,
            'password'  => bcrypt('password'),
        ]);
    }

    private function createOrder(User $customer, User $designer): Order
    {
        $product = Product::create(['name' => 'Produk Test ' . uniqid(), 'is_active' => true]);
        $tier = ProductTier::create(['product_id' => $product->id, 'name' => 'Tier', 'price' => 100000]);

        return Order::create([
            'order_code'      => 'ORD-' . strtoupper(uniqid()),
            'customer_id'     => $customer->id,
            'designer_id'     => $designer->id,
            'product_id'      => $product->id,
            'product_tier_id' => $tier->id,
            'unit_price'      => 100000,
            'quantity'        => 1,
            'total_price'     => 100000,
            'status'          => Order::STATUS_IN_PROGRESS,
            'deadline'        => now()->addDays(7),
            'brief_note'      => 'Brief test',
            'assigned_at'     => now(),
        ]);
    }

    public function test_created_function_creates_new_designer_stat(): void
    {
        $customer = $this->createUser('customer');
        $designer = $this->createUser('designer');
        $order    = $this->createOrder($customer, $designer);

        $this->assertDatabaseMissing('designer_stats', ['designer_id' => $designer->id]);

        OrderSplit::create([
            'order_id'          => $order->id,
            'designer_id'       => $designer->id,
            'split_type'        => OrderSplit::TYPE_NORMAL,
            'commission_rate'   => OrderSplit::COMMISSION_NORMAL,
            'gross_amount'      => 100000,
            'commission_amount' => 10000,
            'designer_earning'  => 90000,
        ]);

        $this->assertDatabaseHas('designer_stats', [
            'designer_id'   => $designer->id,
            'total_earning' => 90000,
        ]);
    }

    public function test_updated_function_updates_existing_designer_stat(): void
    {
        $customer = $this->createUser('customer');
        $designer = $this->createUser('designer');
        $order    = $this->createOrder($customer, $designer);

        $split = OrderSplit::create([
            'order_id'          => $order->id,
            'designer_id'       => $designer->id,
            'split_type'        => OrderSplit::TYPE_NORMAL,
            'commission_rate'   => OrderSplit::COMMISSION_NORMAL,
            'gross_amount'      => 100000,
            'commission_amount' => 10000,
            'designer_earning'  => 90000,
        ]);

        $split->update(['designer_earning' => 150000]);

        $this->assertDatabaseHas('designer_stats', [
            'designer_id'   => $designer->id,
            'total_earning' => 150000,
        ]);
    }

    public function test_deleted_function_removes_designer_stat(): void
    {
        $customer = $this->createUser('customer');
        $designer = $this->createUser('designer');
        $order    = $this->createOrder($customer, $designer);

        $split = OrderSplit::create([
            'order_id'          => $order->id,
            'designer_id'       => $designer->id,
            'split_type'        => OrderSplit::TYPE_NORMAL,
            'commission_rate'   => OrderSplit::COMMISSION_NORMAL,
            'gross_amount'      => 100000,
            'commission_amount' => 10000,
            'designer_earning'  => 90000,
        ]);

        $this->assertDatabaseHas('designer_stats', ['designer_id' => $designer->id, 'total_earning' => 90000]);

        $split->delete();

        $this->assertDatabaseHas('designer_stats', [
            'designer_id'   => $designer->id,
            'total_earning' => 0,
        ]);
    }

    public function test_recalc_earning_function_sums_multiple_splits(): void
    {
        $customer = $this->createUser('customer');
        $designer = $this->createUser('designer');
        $order1   = $this->createOrder($customer, $designer);
        $order2   = $this->createOrder($customer, $designer);

        OrderSplit::create([
            'order_id'          => $order1->id,
            'designer_id'       => $designer->id,
            'split_type'        => OrderSplit::TYPE_NORMAL,
            'commission_rate'   => OrderSplit::COMMISSION_NORMAL,
            'gross_amount'      => 100000,
            'commission_amount' => 10000,
            'designer_earning'  => 90000,
        ]);

        OrderSplit::create([
            'order_id'          => $order2->id,
            'designer_id'       => $designer->id,
            'split_type'        => OrderSplit::TYPE_REASSIGNMENT,
            'commission_rate'   => OrderSplit::COMMISSION_REASSIGNMENT,
            'gross_amount'      => 100000,
            'commission_amount' => 5000,
            'designer_earning'  => 95000,
        ]);

        $this->assertDatabaseHas('designer_stats', [
            'designer_id'   => $designer->id,
            'total_earning' => 185000,
        ]);
    }

    public function test_recalc_earning_function_updates_existing_stat_not_duplicate(): void
    {
        $customer = $this->createUser('customer');
        $designer = $this->createUser('designer');
        $order    = $this->createOrder($customer, $designer);

        OrderSplit::create([
            'order_id'          => $order->id,
            'designer_id'       => $designer->id,
            'split_type'        => OrderSplit::TYPE_NORMAL,
            'commission_rate'   => OrderSplit::COMMISSION_NORMAL,
            'gross_amount'      => 100000,
            'commission_amount' => 10000,
            'designer_earning'  => 90000,
        ]);

        $split = OrderSplit::where('designer_id', $designer->id)->first();
        $split->delete();
        
        OrderSplit::create([
            'order_id'          => $order->id,
            'designer_id'       => $designer->id,
            'split_type'        => OrderSplit::TYPE_NORMAL,
            'commission_rate'   => OrderSplit::COMMISSION_NORMAL,
            'gross_amount'      => 50000,
            'commission_amount' => 5000,
            'designer_earning'  => 45000,
        ]);

        $this->assertDatabaseCount('designer_stats', 1);
        $this->assertDatabaseHas('designer_stats', [
            'designer_id'   => $designer->id,
            'total_earning' => 45000,
        ]);
    }
}