<?php

namespace Tests\Feature\Admin;

use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderLog;
use App\Models\OrderRefund;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use App\Services\R2StorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Mockery\MockInterface;
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
            ->patch(route('admin.monitoring.pembayaran.approve', $payment));

        $response->assertRedirect(route('admin.monitoring'));
        $response->assertSessionHas('success');

        $this->assertEquals(Payment::STATUS_BERHASIL, $payment->fresh()->status);
        $this->assertEquals(Order::STATUS_WAITING_DESIGNER, $payment->order->fresh()->status);
        $this->assertNotNull($payment->fresh()->paid_at);
    }

    public function test_approve_with_existing_designer_sets_order_in_progress(): void
    {
        $this->withoutExceptionHandling();

        $admin = $this->createUser(User::ROLE_ADMIN);
        $payment = $this->createPayment(['withDesigner' => true]);

        $this->assertNotNull($payment->order->designer_id);

        $response = $this->actingAs($admin)
            ->from(route('admin.monitoring'))
            ->patch(route('admin.monitoring.pembayaran.approve', $payment));

        $response->assertRedirect(route('admin.monitoring'));
        $response->assertSessionHas('success');

        $this->assertEquals(Order::STATUS_IN_PROGRESS, $payment->order->fresh()->status);
    }

    public function test_approve_already_success_payment_returns_error(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN);
        $payment = $this->createPayment(['paymentStatus' => Payment::STATUS_BERHASIL]);

        $response = $this->actingAs($admin)
            ->from(route('admin.monitoring'))
            ->patch(route('admin.monitoring.pembayaran.approve', $payment));

        $response->assertSessionHas('error');
        $this->assertEquals(Payment::STATUS_BERHASIL, $payment->fresh()->status);
    }

    public function test_approve_sets_assigned_at_when_empty(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN);
        $payment = $this->createPayment();

        $this->assertNull($payment->order->assigned_at);

        $this->actingAs($admin)
            ->patch(route('admin.monitoring.pembayaran.approve', $payment));

        $this->assertNotNull($payment->order->fresh()->assigned_at);
    }

    public function test_approve_does_not_overwrite_existing_assigned_at(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN);
        $payment = $this->createPayment(['withDesigner' => true]);

        $original = now()->subDays(3)->startOfSecond();
        $payment->order->forceFill(['assigned_at' => $original])->save();

        $this->actingAs($admin)
            ->patch(route('admin.monitoring.pembayaran.approve', $payment));

        $this->assertTrue($payment->order->fresh()->assigned_at->equalTo($original));
    }

    public function test_approve_order_payment_does_not_create_revision_log_or_notification(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN);
        $payment = $this->createPayment();

        $notifsBefore = Notification::count();
        $logsBefore   = OrderLog::count();

        $this->actingAs($admin)
            ->patch(route('admin.monitoring.pembayaran.approve', $payment));

        $this->assertEquals($notifsBefore, Notification::count(),
            'Approve order payment tidak boleh bikin notifikasi baru');
        $this->assertEquals($logsBefore, OrderLog::count(),
            'Approve order payment tidak boleh bikin log baru');

        $this->assertEquals(0, $payment->order->fresh()->paid_revision_quota);
        $this->assertFalse(
            OrderLog::where('action', 'revision_payment_approved')->exists()
        );
    }

    public function test_approve_rolls_back_payment_when_order_update_fails(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN);
        $payment = $this->createPayment();

        Order::updating(function () {
            throw new \Exception('simulasi error');
        });

        $response = $this->actingAs($admin)
            ->from(route('admin.monitoring'))
            ->patch(route('admin.monitoring.pembayaran.approve', $payment));

        $response->assertRedirect(route('admin.monitoring'));
        $response->assertSessionHas('error');

        $this->assertEquals(Payment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertNull($payment->fresh()->paid_at);
    }

    public function test_approve_revision_fee_increments_paid_revision_quota(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN);
        $payment = $this->createRevisionFeePayment();

        $before = (int) $payment->order->paid_revision_quota;

        $response = $this->actingAs($admin)
            ->from(route('admin.monitoring'))
            ->patch(route('admin.monitoring.pembayaran.approve', $payment));

        $response->assertRedirect(route('admin.monitoring'));
        $response->assertSessionHas('success');

        $this->assertEquals(Payment::STATUS_BERHASIL, $payment->fresh()->status);
        $this->assertEquals($before + 1, $payment->order->fresh()->paid_revision_quota);
    }

    public function test_approve_revision_fee_does_not_change_order_status(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN);
        $payment = $this->createRevisionFeePayment();

        $this->actingAs($admin)
            ->patch(route('admin.monitoring.pembayaran.approve', $payment));

        $this->assertEquals(Order::STATUS_DELIVERABLE_SENT, $payment->order->fresh()->status);
    }

    public function test_approve_revision_fee_creates_order_log_and_notification(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN);
        $payment = $this->createRevisionFeePayment();
        $order = $payment->order;

        $this->actingAs($admin)
            ->patch(route('admin.monitoring.pembayaran.approve', $payment));

        $this->assertTrue(
            OrderLog::where('order_id', $order->id)
                ->where('actor_id', $admin->id)
                ->where('action', 'revision_payment_approved')
                ->exists()
        );

        $this->assertTrue(
            Notification::where('user_id', $order->customer_id)
                ->where('type', Notification::TYPE_PAYMENT)
                ->exists()
        );
    }

    public function test_reject_sets_payment_gagal_and_order_stays_pending(): void
    {
        $this->withoutExceptionHandling();

        $admin = $this->createUser(User::ROLE_ADMIN);
        $payment = $this->createPayment();

        $response = $this->actingAs($admin)
            ->from(route('admin.monitoring'))
            ->patch(route('admin.monitoring.pembayaran.reject', $payment));

        $response->assertRedirect(route('admin.monitoring'));
        $response->assertSessionHas('warning');

        $this->assertEquals(Payment::STATUS_GAGAL, $payment->fresh()->status);
        $this->assertEquals(Order::STATUS_PENDING, $payment->order->fresh()->status);
        $this->assertNull($payment->fresh()->paid_at);
    }

    public function test_reject_revision_fee_does_not_add_revision_quota(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN);
        $payment = $this->createRevisionFeePayment();

        $this->actingAs($admin)
            ->patch(route('admin.monitoring.pembayaran.reject', $payment));

        $this->assertEquals(Payment::STATUS_GAGAL, $payment->fresh()->status);
        $this->assertEquals(0, $payment->order->fresh()->paid_revision_quota);
    }

    public function test_customer_cannot_approve_or_reject_payment(): void
    {
        $customer = $this->createUser(User::ROLE_CUSTOMER);
        $payment = $this->createPayment();

        $this->actingAs($customer)
            ->patch(route('admin.monitoring.pembayaran.approve', $payment))
            ->assertForbidden();

        $this->actingAs($customer)
            ->patch(route('admin.monitoring.pembayaran.reject', $payment))
            ->assertForbidden();

        $this->assertEquals(Payment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertEquals(Order::STATUS_PENDING, $payment->order->fresh()->status);
    }

    public function test_designer_cannot_approve_or_reject_payment(): void
    {
        $designer = $this->createUser(User::ROLE_DESIGNER);
        $payment = $this->createPayment();

        $this->actingAs($designer)
            ->patch(route('admin.monitoring.pembayaran.approve', $payment))
            ->assertForbidden();

        $this->actingAs($designer)
            ->patch(route('admin.monitoring.pembayaran.reject', $payment))
            ->assertForbidden();

        $this->assertEquals(Payment::STATUS_PENDING, $payment->fresh()->status);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $payment = $this->createPayment();
        $refund = $this->createRefund();

        $this->get(route('admin.monitoring'))->assertRedirect(route('login'));

        $this->patch(route('admin.monitoring.pembayaran.approve', $payment))
            ->assertRedirect(route('login'));

        $this->patch(route('admin.monitoring.pembayaran.reject', $payment))
            ->assertRedirect(route('login'));

        $this->patch(route('admin.monitoring.refund.selesai', $refund))
            ->assertRedirect(route('login'));

        $this->assertEquals(Payment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertTrue($refund->fresh()->isPending());
    }

    public function test_admin_can_view_monitoring_page_with_stats(): void
    {
        $this->mockR2();
        $admin = $this->createUser(User::ROLE_ADMIN);

        $this->createPayment();
        $this->createPayment(['paymentStatus' => Payment::STATUS_BERHASIL]);
        $this->createPayment(['orderStatus' => Order::STATUS_COMPLETED]);

        $response = $this->actingAs($admin)->get(route('admin.monitoring'));

        $response->assertOk();
        $response->assertViewIs('admin.monitoring');
        $response->assertViewHas('stats', function (array $stats) {
            return $stats['pending_payments'] === 2
                && $stats['completed_orders'] === 1
                && $stats['active_orders'] === 2;
        });
        $response->assertViewHasAll(['orders', 'payments']);
    }

    public function test_late_orders_stat_counts_only_active_orders_past_deadline(): void
    {
        $this->mockR2();
        $admin = $this->createUser(User::ROLE_ADMIN);

        $late = $this->createPayment(['orderStatus' => Order::STATUS_IN_PROGRESS]);
        $late->order->forceFill(['deadline' => now()->subDay()])->save();

        $onTime = $this->createPayment(['orderStatus' => Order::STATUS_IN_PROGRESS]);
        $onTime->order->forceFill(['deadline' => now()->addDay()])->save();

        $completedLate = $this->createPayment(['orderStatus' => Order::STATUS_COMPLETED]);
        $completedLate->order->forceFill(['deadline' => now()->subDay()])->save();

        $this->actingAs($admin)
            ->get(route('admin.monitoring'))
            ->assertViewHas('stats', fn (array $s) => $s['late_orders'] === 1);
    }

    public function test_monitoring_filters_orders_by_status(): void
    {
        $this->mockR2();
        $admin = $this->createUser(User::ROLE_ADMIN);

        $this->createPayment(['orderStatus' => Order::STATUS_PENDING]);
        $inProgress = $this->createPayment(['orderStatus' => Order::STATUS_IN_PROGRESS]);

        $this->actingAs($admin)
            ->get(route('admin.monitoring', ['status_order' => Order::STATUS_IN_PROGRESS]))
            ->assertViewHas('orders', function ($orders) use ($inProgress) {
                return $orders->count() === 1
                    && $orders->first()->id === $inProgress->order_id;
            });
    }

    public function test_monitoring_search_by_order_code(): void
    {
        $this->mockR2();
        $admin = $this->createUser(User::ROLE_ADMIN);

        $target = $this->createPayment();
        $target->order->forceFill(['order_code' => 'ORD-CARI-ME'])->save();
        $this->createPayment();

        $this->actingAs($admin)
            ->get(route('admin.monitoring', ['q' => 'cari-me']))
            ->assertViewHas('orders', fn ($orders) => $orders->count() === 1
                && $orders->first()->order_code === 'ORD-CARI-ME');
    }

    public function test_payments_with_proof_get_temporary_url(): void
    {
        $this->mockR2('https://r2.test/signed-url');
        $admin = $this->createUser(User::ROLE_ADMIN);

        $payment = $this->createPayment();
        $payment->update(['proof_url' => 'payment-proofs/bukti.png']);

        $this->actingAs($admin)
            ->get(route('admin.monitoring'))
            ->assertViewHas('payments', fn ($payments) => $payments->first()->proof_url_temp
                === 'https://r2.test/signed-url');
    }

    public function test_monitoring_page_still_loads_when_r2_fails(): void
    {
        $this->mock(R2StorageService::class, function (MockInterface $mock) {
            $mock->shouldReceive('temporaryUrlPrivate')->andThrow(new \Exception('R2 down'));
        });
        $admin = $this->createUser(User::ROLE_ADMIN);

        $payment = $this->createPayment();
        $payment->update(['proof_url' => 'payment-proofs/bukti.png']);

        $this->actingAs($admin)
            ->get(route('admin.monitoring'))
            ->assertOk()
            ->assertViewHas('payments', fn ($payments) => $payments->first()->proof_url_temp === null);
    }

    public function test_old_non_pending_payments_are_excluded_from_list(): void
    {
        $this->mockR2();
        $admin = $this->createUser(User::ROLE_ADMIN);

        $old = $this->createPayment(['paymentStatus' => Payment::STATUS_BERHASIL]);
        $old->forceFill(['created_at' => now()->subDays(45)])->save();

        $oldPending = $this->createPayment();
        $oldPending->forceFill(['created_at' => now()->subDays(45)])->save();

        $recent = $this->createPayment(['paymentStatus' => Payment::STATUS_BERHASIL]);

        $this->actingAs($admin)
            ->get(route('admin.monitoring'))
            ->assertViewHas('payments', function ($payments) use ($old, $oldPending, $recent) {
                $ids = $payments->pluck('id');

                return ! $ids->contains($old->id)
                    && $ids->contains($oldPending->id)
                    && $ids->contains($recent->id);
            });
    }

    public function test_selesaikan_refund_marks_refund_completed_and_order_refunded(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN);
        $refund = $this->createRefund();
        $this->mockR2(uploadPath: 'refund-proofs/bukti.png');

        $response = $this->actingAs($admin)
            ->from(route('admin.monitoring'))
            ->patch(route('admin.monitoring.refund.selesai', $refund), [
                'proof' => UploadedFile::fake()->image('bukti.png'),
            ]);

        $response->assertRedirect(route('admin.monitoring'));
        $response->assertSessionHas('success');

        $refund = $refund->fresh();

        $this->assertTrue($refund->isCompleted());
        $this->assertEquals(OrderRefund::STATUS_COMPLETED, $refund->status);
        $this->assertEquals('refund-proofs/bukti.png', $refund->proof_url);
        $this->assertEquals($admin->id, $refund->processed_by);
        $this->assertNotNull($refund->processed_at);
        $this->assertNotNull($refund->proof_uploaded_at);

        $this->assertEquals(Order::STATUS_REFUNDED, $refund->order->fresh()->status);
    }

    public function test_selesaikan_refund_creates_log_and_notification(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN);
        $refund = $this->createRefund();
        $this->mockR2();

        $this->actingAs($admin)
            ->patch(route('admin.monitoring.refund.selesai', $refund), [
                'proof' => UploadedFile::fake()->image('bukti.png'),
            ]);

        $order = $refund->order;

        $this->assertTrue(
            OrderLog::where('order_id', $order->id)
                ->where('actor_id', $admin->id)
                ->where('action', OrderLog::ACTION_REFUND_COMPLETED)
                ->where('status', Order::STATUS_REFUNDED)
                ->exists()
        );

        $this->assertTrue(
            Notification::where('user_id', $order->customer_id)
                ->where('type', Notification::TYPE_REFUND_COMPLETED)
                ->exists()
        );
    }

    public function test_selesaikan_refund_already_completed_returns_error_without_upload(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN);
        $refund = $this->createRefund();
        $refund->markAsCompleted($admin, 'refund-proofs/lama.png');

        $this->mock(R2StorageService::class, function (MockInterface $mock) {
            $mock->shouldNotReceive('upload');
        });

        $response = $this->actingAs($admin)
            ->from(route('admin.monitoring'))
            ->patch(route('admin.monitoring.refund.selesai', $refund), [
                'proof' => UploadedFile::fake()->image('bukti.png'),
            ]);

        $response->assertSessionHas('error');
        $this->assertEquals('refund-proofs/lama.png', $refund->fresh()->proof_url);
    }

    public function test_selesaikan_refund_requires_proof(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN);
        $refund = $this->createRefund();

        $this->actingAs($admin)
            ->from(route('admin.monitoring'))
            ->patch(route('admin.monitoring.refund.selesai', $refund), [])
            ->assertSessionHasErrors('proof');

        $this->assertTrue($refund->fresh()->isPending());
    }

    public function test_selesaikan_refund_rejects_invalid_file_type(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN);
        $refund = $this->createRefund();

        $this->actingAs($admin)
            ->from(route('admin.monitoring'))
            ->patch(route('admin.monitoring.refund.selesai', $refund), [
                'proof' => UploadedFile::fake()->create('virus.exe', 100),
            ])
            ->assertSessionHasErrors('proof');

        $this->assertTrue($refund->fresh()->isPending());
    }

    public function test_selesaikan_refund_rejects_file_larger_than_5mb(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN);
        $refund = $this->createRefund();

        $this->actingAs($admin)
            ->from(route('admin.monitoring'))
            ->patch(route('admin.monitoring.refund.selesai', $refund), [
                'proof' => UploadedFile::fake()->create('besar.pdf', 6000, 'application/pdf'),
            ])
            ->assertSessionHasErrors('proof');

        $this->assertTrue($refund->fresh()->isPending());
    }

    public function test_selesaikan_refund_accepts_pdf_proof(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN);
        $refund = $this->createRefund();
        $this->mockR2(uploadPath: 'refund-proofs/bukti.pdf');

        $this->actingAs($admin)
            ->patch(route('admin.monitoring.refund.selesai', $refund), [
                'proof' => UploadedFile::fake()->create('bukti.pdf', 500, 'application/pdf'),
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue($refund->fresh()->isCompleted());
    }

    public function test_selesaikan_refund_rolls_back_when_upload_fails(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN);
        $refund = $this->createRefund();

        $this->mock(R2StorageService::class, function (MockInterface $mock) {
            $mock->shouldReceive('upload')->andThrow(new \Exception('R2 down'));
        });

        $response = $this->actingAs($admin)
            ->from(route('admin.monitoring'))
            ->patch(route('admin.monitoring.refund.selesai', $refund), [
                'proof' => UploadedFile::fake()->image('bukti.png'),
            ]);

        $response->assertSessionHas('error');
        $this->assertTrue($refund->fresh()->isPending());
        $this->assertNotEquals(Order::STATUS_REFUNDED, $refund->order->fresh()->status);
    }

    public function test_selesaikan_refund_rolls_back_refund_when_order_update_fails(): void
    {
        $admin = $this->createUser(User::ROLE_ADMIN);
        $refund = $this->createRefund();
        $this->mockR2();

        Order::updating(function () {
            throw new \Exception('simulasi error');
        });

        $response = $this->actingAs($admin)
            ->from(route('admin.monitoring'))
            ->patch(route('admin.monitoring.refund.selesai', $refund), [
                'proof' => UploadedFile::fake()->image('bukti.png'),
            ]);

        $response->assertSessionHas('error');

        $refund = $refund->fresh();

        $this->assertTrue($refund->isPending());
        $this->assertNull($refund->processed_by);
        $this->assertNull($refund->proof_url);
        $this->assertEquals(
            0,
            OrderLog::where('action', OrderLog::ACTION_REFUND_COMPLETED)->count()
        );
        $this->assertEquals(
            0,
            Notification::where('type', Notification::TYPE_REFUND_COMPLETED)->count()
        );
    }

    public function test_customer_cannot_complete_refund(): void
    {
        $customer = $this->createUser(User::ROLE_CUSTOMER);
        $refund = $this->createRefund();

        $this->actingAs($customer)
            ->patch(route('admin.monitoring.refund.selesai', $refund), [
                'proof' => UploadedFile::fake()->image('bukti.png'),
            ])
            ->assertForbidden();

        $this->assertTrue($refund->fresh()->isPending());
    }

    public function test_designer_cannot_complete_refund(): void
    {
        $designer = $this->createUser(User::ROLE_DESIGNER);
        $refund = $this->createRefund();

        $this->actingAs($designer)
            ->patch(route('admin.monitoring.refund.selesai', $refund), [
                'proof' => UploadedFile::fake()->image('bukti.png'),
            ])
            ->assertForbidden();

        $this->assertTrue($refund->fresh()->isPending());
    }

    private function mockR2(
        string $tempUrl = 'https://r2.test/temp',
        string $uploadPath = 'refund-proofs/bukti.png'
    ): void {
        $this->mock(R2StorageService::class, function (MockInterface $mock) use ($tempUrl, $uploadPath) {
            $mock->shouldReceive('temporaryUrlPrivate')->andReturn($tempUrl);
            $mock->shouldReceive('upload')->andReturn($uploadPath);
        });
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
        $type          = $options['type']          ?? Payment::TYPE_ORDER;

        $customer = $this->createUser(User::ROLE_CUSTOMER);
        $designer = $withDesigner ? $this->createUser(User::ROLE_DESIGNER) : null;

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
            'type'     => $type,
            'method'   => 'transfer',
            'amount'   => 50000,
            'status'   => $paymentStatus,
        ]);
    }

    private function createRevisionFeePayment(): Payment
    {
        return $this->createPayment([
            'type'         => Payment::TYPE_REVISION_FEE,
            'orderStatus'  => Order::STATUS_DELIVERABLE_SENT,
            'withDesigner' => true,
        ]);
    }

    private function createRefund(): OrderRefund
    {
        $payment = $this->createPayment([
            'paymentStatus' => Payment::STATUS_BERHASIL,
            'orderStatus'   => Order::STATUS_IN_PROGRESS,
            'withDesigner'  => true,
        ]);
        $order = $payment->order;

        return OrderRefund::create([
            'order_id'            => $order->id,
            'customer_id'         => $order->customer_id,
            'bank_name'           => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_name'   => 'Test Customer',
            'amount'              => 50000,
            'status'              => OrderRefund::STATUS_PENDING,
        ]);
    }
}