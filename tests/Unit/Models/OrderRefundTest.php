<?php

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\OrderRefund;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderRefundTest extends TestCase
{
    use RefreshDatabase;

    // ============================================================
    // Test #1: isPending() & isCompleted() berdasarkan status
    // ============================================================
    public function test_is_pending_and_is_completed(): void
    {
        $pendingRefund = $this->createRefund([
            'status' => OrderRefund::STATUS_PENDING,
        ]);

        $this->assertTrue($pendingRefund->isPending());
        $this->assertFalse($pendingRefund->isCompleted());

        $completedRefund = $this->createRefund([
            'status' => OrderRefund::STATUS_COMPLETED,
        ]);

        $this->assertFalse($completedRefund->isPending());
        $this->assertTrue($completedRefund->isCompleted());
    }

    // ============================================================
    // Test #2: markAsCompleted() mengubah status + set processed_by, processed_at, proof_url
    // ============================================================
    public function test_mark_as_completed_sets_all_fields(): void
    {
        $refund = $this->createRefund();
        $admin = $this->createUser(User::ROLE_ADMIN);

        $this->assertNull($refund->processed_by);
        $this->assertNull($refund->processed_at);
        $this->assertNull($refund->proof_url);

        $refund->markAsCompleted($admin, 'refund/proof.png');
        $refund->refresh();

        $this->assertEquals(OrderRefund::STATUS_COMPLETED, $refund->status);
        $this->assertEquals($admin->id, $refund->processed_by);
        $this->assertNotNull($refund->processed_at);
        $this->assertEquals('refund/proof.png', $refund->proof_url);
        $this->assertNotNull($refund->proof_uploaded_at);
    }

    // ============================================================
    // Test #3: markAsCompleted() tanpa proof → proof_url null, proof_uploaded_at null
    // ============================================================
    public function test_mark_as_completed_without_proof(): void
    {
        $refund = $this->createRefund();
        $admin = $this->createUser(User::ROLE_ADMIN);

        $refund->markAsCompleted($admin); // tanpa proof
        $refund->refresh();

        $this->assertEquals(OrderRefund::STATUS_COMPLETED, $refund->status);
        $this->assertEquals($admin->id, $refund->processed_by);
        $this->assertNull($refund->proof_url);
        $this->assertNull($refund->proof_uploaded_at);
    }

    // ============================================================
    // Test #4: Relasi order(), customer(), processor()
    // ============================================================
    public function test_relations_return_correct_models(): void
    {
        $refund = $this->createRefund();

        // order()
        $relatedOrder = $refund->order;
        $this->assertInstanceOf(Order::class, $relatedOrder);
        $this->assertEquals($refund->order_id, $relatedOrder->id);

        // customer()
        $relatedCustomer = $refund->customer;
        $this->assertInstanceOf(User::class, $relatedCustomer);
        $this->assertEquals($refund->customer_id, $relatedCustomer->id);

        // processor() — null karena belum diproses
        $this->assertNull($refund->processor);
    }

    // ============================================================
    // Test #5: processor() mengembalikan admin setelah markAsCompleted()
    // ============================================================
    public function test_processor_relation_returns_admin_after_completed(): void
    {
        $refund = $this->createRefund();
        $admin = $this->createUser(User::ROLE_ADMIN);

        $refund->markAsCompleted($admin, 'refund/proof.png');
        $refund->refresh();

        $relatedProcessor = $refund->processor;

        $this->assertInstanceOf(User::class, $relatedProcessor);
        $this->assertEquals($admin->id, $relatedProcessor->id);
        $this->assertEquals(User::ROLE_ADMIN, $relatedProcessor->role);
    }

    // ============================================================
    // Test #6: Cast amount ke decimal string
    // ============================================================
    public function test_amount_is_cast_to_decimal(): void
    {
        $refund = $this->createRefund([
            'amount' => 75000.50,
        ]);

        $refund->refresh();

        $this->assertEquals('75000.50', $refund->amount);
    }

    // ============================================================
    // Test #7: Cast processed_at & proof_uploaded_at ke Carbon
    // ============================================================
    public function test_datetime_columns_are_cast_to_carbon(): void
    {
        $refund = $this->createRefund();
        $admin = $this->createUser(User::ROLE_ADMIN);

        $refund->markAsCompleted($admin, 'refund/proof.png');
        $refund->refresh();

        $this->assertInstanceOf(\Carbon\Carbon::class, $refund->processed_at);
        $this->assertInstanceOf(\Carbon\Carbon::class, $refund->proof_uploaded_at);
    }

    // ============================================================
    // HELPER
    // ============================================================
    private function createUser(string $role = User::ROLE_CUSTOMER): User
    {
        return User::create([
            'username'  => 'user_' . uniqid(),
            'email'     => 'user_' . uniqid() . '@test.com',
            'full_name' => 'Test ' . ucfirst($role),
            'phone'     => '08123456789',
            'role'      => $role,
            'password'  => bcrypt('password'),
        ]);
    }

    private function createOrder(): Order
    {
        $customer = $this->createUser(User::ROLE_CUSTOMER);
        $product = Product::create([
            'name'          => 'Test Product ' . uniqid(),
            'description'   => 'Deskripsi test',
            'thumbnail_url' => 'products/test.png',
            'price'         => 50000,
            'is_active'     => true,
        ]);

        return Order::create([
            'order_code'  => 'ORD-' . uniqid(),
            'customer_id' => $customer->id,
            'designer_id' => null,
            'product_id'  => $product->id,
            'unit_price'  => 50000,
            'quantity'    => 1,
            'total_price' => 50000,
            'status'      => Order::STATUS_PENDING,
        ]);
    }

    private function createRefund(array $overrides = []): OrderRefund
    {
        $order = $this->createOrder();
        $customer = $order->customer;

        return OrderRefund::create(array_merge([
            'order_id'            => $order->id,
            'customer_id'         => $customer->id,
            'bank_name'           => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name'   => 'Test Customer',
            'amount'              => 50000,
            'status'              => OrderRefund::STATUS_PENDING,
            'proof_url'           => null,
            'proof_uploaded_at'   => null,
            'processed_by'        => null,
            'processed_at'        => null,
        ], $overrides));
    }
}