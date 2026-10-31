<?php

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_success_returns_true_for_berhasil_status(): void
    {
        $payment = $this->createPayment([
            'status' => Payment::STATUS_BERHASIL,
        ]);

        $this->assertTrue($payment->isSuccess());
        $this->assertFalse($payment->isPending());
    }

    public function test_is_order_payment_vs_revision_fee_type(): void
    {
        $orderPayment = $this->createPayment([
            'type' => Payment::TYPE_ORDER,
        ]);

        $revisionPayment = $this->createPayment([
            'type' => Payment::TYPE_REVISION_FEE,
        ]);

        $this->assertTrue($orderPayment->isOrderPayment());
        $this->assertFalse($orderPayment->isRevisionFee());

        $this->assertTrue($revisionPayment->isRevisionFee());
        $this->assertFalse($revisionPayment->isOrderPayment());
    }

    public function test_is_pending_returns_true_for_pending_status(): void
    {
        $payment = $this->createPayment([
            'status' => Payment::STATUS_PENDING,
        ]);

        $this->assertTrue($payment->isPending());
        $this->assertFalse($payment->isSuccess());
    }

    public function test_mark_as_success_updates_status_and_paid_at(): void
    {
        $payment = $this->createPayment([
            'status' => Payment::STATUS_PENDING,
        ]);

        $payment->markAsSuccess();
        $payment->refresh();

        $this->assertEquals(Payment::STATUS_BERHASIL, $payment->status);
        $this->assertNotNull($payment->paid_at);
    }

    public function test_scope_success_filters_only_berhasil(): void
    {
        $this->createPayment(['status' => Payment::STATUS_PENDING]);
        $this->createPayment(['status' => Payment::STATUS_BERHASIL]);
        $this->createPayment(['status' => Payment::STATUS_GAGAL]);

        $this->assertEquals(1, Payment::success()->count());
    }

    private function createUser(string $role): User
    {
        return User::create([
            'username'  => $role . '_' . uniqid(),
            'email'     => $role . '_' . uniqid() . '@test.com',
            'full_name' => 'Test ' . ucfirst($role),
            'phone'     => '08123456789',
            'role'      => $role,
            'password'  => bcrypt('password'),
        ]);
    }

    private function createPayment(array $overrides = []): Payment
    {
        $customer = $this->createUser(User::ROLE_CUSTOMER);

        $product = Product::create([
            'name'          => 'Test Product ' . uniqid(),
            'description'   => 'Deskripsi test',
            'thumbnail_url' => 'products/test.png',
            'price'         => 50000,
            'is_active'     => true,
        ]);

        $order = Order::create([
            'order_code'  => 'ORD-' . uniqid(),
            'customer_id' => $customer->id,
            'product_id'  => $product->id,
            'unit_price'  => 50000,
            'quantity'    => 1,
            'total_price' => 50000,
            'status'      => Order::STATUS_PENDING,
        ]);

        return Payment::create(array_merge([
            'order_id' => $order->id,
            'type'     => Payment::TYPE_ORDER,
            'method'   => 'transfer',
            'amount'   => 50000,
            'status'   => Payment::STATUS_PENDING,
        ], $overrides));
    }
}