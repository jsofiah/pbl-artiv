<?php

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\OrderLog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderLogTest extends TestCase
{
    use RefreshDatabase;

    // ============================================================
    // Test #1: Relasi order() mengembalikan Order yang benar
    // ============================================================
    public function test_order_relation_returns_correct_order(): void
    {
        $orderLog = $this->createOrderLog();

        $relatedOrder = $orderLog->order;

        $this->assertInstanceOf(Order::class, $relatedOrder);
        $this->assertEquals($orderLog->order_id, $relatedOrder->id);
        $this->assertEquals($orderLog->order->order_code, $relatedOrder->order_code);
    }

    // ============================================================
    // Test #2: Relasi actor() mengembalikan User yang benar
    // ============================================================
    public function test_actor_relation_returns_correct_user(): void
    {
        $orderLog = $this->createOrderLog();

        $relatedActor = $orderLog->actor;

        $this->assertInstanceOf(User::class, $relatedActor);
        $this->assertEquals($orderLog->actor_id, $relatedActor->id);
    }

    // ============================================================
    // Test #3: Mass assignment mengisi semua kolom $fillable
    // ============================================================
    public function test_mass_assignment_fills_all_fillable_columns(): void
    {
        $orderLog = $this->createOrderLog();

        $this->assertEquals('create', $orderLog->action);
        $this->assertEquals(Order::STATUS_PENDING, $orderLog->status);
        $this->assertEquals('Order dibuat oleh customer', $orderLog->note);
        $this->assertNotNull($orderLog->order_id);
        $this->assertNotNull($orderLog->actor_id);
    }

    // ============================================================
    // Test #4: Semua konstanta ACTION valid (tidak ada typo)
    // ============================================================
    public function test_action_constants_are_defined(): void
    {
        $expectedActions = [
            'create',
            'assign',
            'reassign',
            'deliverable_sent',
            'revision_requested',
            'completed',
            'cancelled',
            'deadline_missed',
            'cannot_continue',
            'customer_choose_refund',
            'customer_choose_continue',
            'refund_completed',
            'designer_cancel',
            'customer_refund',
        ];

        $reflection = new \ReflectionClass(OrderLog::class);
        $constants = $reflection->getConstants();

        foreach ($expectedActions as $action) {
            $this->assertContains(
                $action,
                $constants,
                "Konstanta untuk action '{$action}' tidak ditemukan"
            );
        }
    }

    // ============================================================
    // Test #5: UUID auto-generate
    // ============================================================
    public function test_id_is_uuid_auto_generated(): void
    {
        $orderLog = $this->createOrderLog();

        $this->assertNotNull($orderLog->id);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $orderLog->id
        );
    }

    // ============================================================
    // Test #6: Kolom nullable (note, status) boleh null
    // ============================================================
    public function test_nullable_columns_can_be_null(): void
    {
        $order = $this->createOrder();
        $actor = $this->createUser();

        $orderLog = OrderLog::create([
            'order_id' => $order->id,
            'actor_id' => $actor->id,
            'action'   => OrderLog::ACTION_ASSIGN,
            'status'   => null,
            'note'     => null,
        ]);

        $this->assertNull($orderLog->status);
        $this->assertNull($orderLog->note);
        $this->assertNotNull($orderLog->action);
    }

    // ============================================================
    // HELPER
    // ============================================================
    private function createUser(string $role = User::ROLE_CUSTOMER): User
    {
        return User::create([
            'username'  => 'user_' . uniqid(),
            'email'     => 'user_' . uniqid() . '@test.com',
            'full_name' => 'Test User',
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

    private function createOrderLog(): OrderLog
    {
        $order = $this->createOrder();
        $actor = $this->createUser(User::ROLE_CUSTOMER);

        return OrderLog::create([
            'order_id' => $order->id,
            'actor_id' => $actor->id,
            'action'   => OrderLog::ACTION_CREATE,
            'status'   => Order::STATUS_PENDING,
            'note'     => 'Order dibuat oleh customer',
        ]);
    }
}