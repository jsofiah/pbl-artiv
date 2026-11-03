<?php

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
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

    public function test_gagal_status_is_neither_success_nor_pending(): void
    {
        $payment = $this->createPayment([
            'status' => Payment::STATUS_GAGAL,
        ]);

        $this->assertFalse($payment->isSuccess());
        $this->assertFalse($payment->isPending());
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

    public function test_mark_as_success_sets_paid_at_to_current_time(): void
    {
        Carbon::setTestNow('2026-11-03 10:00:00');

        $payment = $this->createPayment([
            'status' => Payment::STATUS_PENDING,
        ]);

        $payment->markAsSuccess();
        $payment->refresh();

        $this->assertTrue($payment->paid_at->equalTo(Carbon::now()));

        Carbon::setTestNow(); // reset
    }

    public function test_scope_success_filters_only_berhasil(): void
    {
        $this->createPayment(['status' => Payment::STATUS_PENDING]);
        $this->createPayment(['status' => Payment::STATUS_BERHASIL]);
        $this->createPayment(['status' => Payment::STATUS_GAGAL]);

        $this->assertEquals(1, Payment::success()->count());
    }

    public function test_payment_belongs_to_order(): void
    {
        $payment = $this->createPayment();

        $this->assertInstanceOf(BelongsTo::class, $payment->order());
        $this->assertInstanceOf(Order::class, $payment->order);
        $this->assertEquals($payment->order_id, $payment->order->id);
    }

    public function test_amount_is_cast_to_decimal_with_two_places(): void
    {
        $payment = $this->createPayment(['amount' => 50000]);
        $payment->refresh();

        $this->assertEquals('50000.00', $payment->amount);
    }

    public function test_paid_at_is_cast_to_carbon_instance(): void
    {
        $payment = $this->createPayment([
            'status'  => Payment::STATUS_BERHASIL,
            'paid_at' => '2026-11-03 10:00:00',
        ]);
        $payment->refresh();

        $this->assertInstanceOf(Carbon::class, $payment->paid_at);
    }

    public function test_paid_at_is_null_by_default(): void
    {
        $payment = $this->createPayment();

        $this->assertNull($payment->paid_at);
    }

    public function test_type_constants_have_expected_values(): void
    {
        $this->assertSame('order', Payment::TYPE_ORDER);
        $this->assertSame('revision_fee', Payment::TYPE_REVISION_FEE);
    }

    public function test_status_constants_have_expected_values(): void
    {
        $this->assertSame('pending', Payment::STATUS_PENDING);
        $this->assertSame('berhasil', Payment::STATUS_BERHASIL);
        $this->assertSame('gagal', Payment::STATUS_GAGAL);
    }

    public function test_creating_payment_clears_admin_dashboard_cache(): void
    {
        Cache::put('admin_dashboard_stats', 'cached-data', 600);
        $this->assertTrue(Cache::has('admin_dashboard_stats'));

        $this->createPayment();

        $this->assertFalse(Cache::has('admin_dashboard_stats'));
    }

    public function test_updating_payment_clears_admin_dashboard_cache(): void
    {
        $payment = $this->createPayment();

        Cache::put('admin_dashboard_stats', 'cached-data', 600);

        $payment->update(['status' => Payment::STATUS_GAGAL]);

        $this->assertFalse(Cache::has('admin_dashboard_stats'));
    }

    public function test_mark_as_success_clears_admin_dashboard_cache(): void
    {
        $payment = $this->createPayment();

        Cache::put('admin_dashboard_stats', 'cached-data', 600);

        $payment->markAsSuccess();

        $this->assertFalse(Cache::has('admin_dashboard_stats'));
    }

    public function test_deleting_payment_clears_admin_dashboard_cache(): void
    {
        $payment = $this->createPayment();

        Cache::put('admin_dashboard_stats', 'cached-data', 600);

        $payment->delete();

        $this->assertFalse(Cache::has('admin_dashboard_stats'));
    }

    public function test_payment_uses_uuid_as_primary_key(): void
    {
        $payment = $this->createPayment();

        $this->assertTrue(\Illuminate\Support\Str::isUuid($payment->id));
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