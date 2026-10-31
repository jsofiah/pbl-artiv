<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PembayaranControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_approve_sets_payment_berhasil_and_order_waiting_designer(): void
    {
        $this->withoutExceptionHandling();

        $admin = $this->createUser(User::ROLE_ADMIN);
        $payment = $this->createPayment();

        $this->assertEquals(Payment::STATUS_PENDING, $payment->status);
        $this->assertEquals(Order::STATUS_PENDING, $payment->order->status);
        $this->assertNull($payment->order->designer_id);

        $response = $this->actingAs($admin)
            ->from(route('admin.monitoring'))
            ->csrfPatch(route('admin.monitoring.pembayaran.approve', $payment));

        $response->assertRedirect(route('admin.monitoring'));
        $response->assertSessionHas('success');

        $this->assertEquals(Payment::STATUS_BERHASIL, $payment->fresh()->status);
        $this->assertEquals(Order::STATUS_WAITING_DESIGNER, $payment->order->fresh()->status);
        $this->assertNotNull($payment->fresh()->paid_at);
    }

    public function test_reject_sets_payment_gagal_and_order_stays_pending(): void
    {
        $this->withoutExceptionHandling();

        $admin = $this->createUser(User::ROLE_ADMIN);
        $payment = $this->createPayment();

        $response = $this->actingAs($admin)
            ->from(route('admin.monitoring'))
            ->csrfPatch(route('admin.monitoring.pembayaran.reject', $payment));

        $response->assertRedirect(route('admin.monitoring'));
        $response->assertSessionHas('warning');

        $this->assertEquals(Payment::STATUS_GAGAL, $payment->fresh()->status);
        $this->assertEquals(Order::STATUS_PENDING, $payment->order->fresh()->status);
        $this->assertNull($payment->fresh()->paid_at);
    }

    public function test_customer_cannot_access_payment_approval_route(): void
    {
        $customer = $this->createUser(User::ROLE_CUSTOMER);
        $payment = $this->createPayment();

        $response = $this->actingAs($customer)
            ->csrfPatch(route('admin.monitoring.pembayaran.approve', $payment));

        $response->assertForbidden();

        $this->assertEquals(Payment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertEquals(Order::STATUS_PENDING, $payment->order->fresh()->status);
    }

    // ============================================================
    // HELPER
    // ============================================================

    private function csrfPatch(string $uri, array $data = [])
    {
        $this->withSession(['_token' => 'test-token']);

        return $this->withHeaders([
            'X-CSRF-TOKEN' => 'test-token',
        ])->patch($uri, $data);
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

    private function createPayment(array $options = []): Payment
    {
        $withDesigner  = $options['withDesigner']  ?? false;
        $paymentStatus = $options['paymentStatus'] ?? Payment::STATUS_PENDING;
        $orderStatus   = $options['orderStatus']   ?? Order::STATUS_PENDING;

        $customer = $this->createUser(User::ROLE_CUSTOMER);

        $designer = $withDesigner
            ? $this->createUser(User::ROLE_DESIGNER)
            : null;

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
            'designer_id' => $designer?->id,
            'product_id'  => $product->id,
            'unit_price'  => 50000,
            'quantity'    => 1,
            'total_price' => 50000,
            'status'      => $orderStatus,
        ]);

        return Payment::create([
            'order_id' => $order->id,
            'type'     => Payment::TYPE_ORDER,
            'method'   => 'transfer',
            'amount'   => 50000,
            'status'   => $paymentStatus,
        ]);
    }

    public function test_approve_with_existing_designer_sets_order_in_progress(): void
    {
        $this->withoutExceptionHandling();

        $admin = $this->createUser(User::ROLE_ADMIN);
        
        $payment = $this->createPayment(['withDesigner' => true]);

        $this->assertNotNull($payment->order->designer_id);

        $response = $this->actingAs($admin)
            ->from(route('admin.monitoring'))
            ->csrfPatch(route('admin.monitoring.pembayaran.approve', $payment));

        $response->assertRedirect(route('admin.monitoring'));
        $response->assertSessionHas('success');

        $this->assertEquals(Order::STATUS_IN_PROGRESS, $payment->order->fresh()->status);
    }

    public function test_approve_already_success_payment_returns_error(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN);
        
        $payment = $this->createPayment([
            'paymentStatus' => Payment::STATUS_BERHASIL,
        ]);

        $this->assertEquals(Payment::STATUS_BERHASIL, $payment->status);

        $response = $this->actingAs($admin)
            ->from(route('admin.monitoring'))
            ->csrfPatch(route('admin.monitoring.pembayaran.approve', $payment));

        $response->assertSessionHas('error');

        $this->assertEquals(Payment::STATUS_BERHASIL, $payment->fresh()->status);
    }
}