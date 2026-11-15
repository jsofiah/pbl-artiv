<?php

namespace Tests\Unit\Observers;

use App\Models\Order;
use App\Observers\OrderObserver;
use App\Services\NotificationService;
use Illuminate\Support\Str;
use Mockery\MockInterface;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

#[RunTestsInSeparateProcesses]
/**
 * Tanpa database. Order dibuat di memori (tidak di-save), lalu perubahan
 * ditandai dengan syncOriginal() + syncChanges() supaya wasChanged() bernilai
 * true seperti setelah save() sungguhan. Observer dipanggil langsung dan
 * NotificationService di-mock.
 */
class OrderObserverTest extends TestCase
{
    /**
     * @param array $original  kondisi order sebelum diubah
     * @param array $changes   kolom yang berubah
     */
    private function makeOrder(array $original, array $changes = []): Order
    {
        $order = new Order();
        $order->forceFill(array_merge([
            'id'          => (string) Str::uuid(),
            'order_code'  => 'ARTIV-TEST0001',
            'customer_id' => (string) Str::uuid(),
            'designer_id' => null,
            'status'      => Order::STATUS_PENDING,
        ], $original));
        $order->syncOriginal();

        $order->forceFill($changes);
        $order->syncChanges();

        return $order;
    }

    public function test_notifies_customer_when_designer_takes_order(): void
    {
        $order = $this->makeOrder(
            ['status' => Order::STATUS_WAITING_DESIGNER],
            ['designer_id' => (string) Str::uuid()]   // status tidak berubah
        );

        $this->mock(NotificationService::class, function (MockInterface $mock) use ($order) {
            $mock->shouldReceive('orderTaken')
                ->once()
                ->with(\Mockery::on(fn ($o) => $o === $order));
        });

        (new OrderObserver())->updated($order);
    }

    public function test_notifies_designers_when_order_waiting_without_designer(): void
    {
        $order = $this->makeOrder(
            ['status' => Order::STATUS_WAITING_CUSTOMER_DECISION],
            ['status' => Order::STATUS_WAITING_DESIGNER]
        );

        $this->mock(NotificationService::class, function (MockInterface $mock) use ($order) {
            $mock->shouldReceive('newOrderAvailable')
                ->once()
                ->with(\Mockery::on(fn ($o) => $o === $order));
        });

        (new OrderObserver())->updated($order);
    }

    public function test_notifies_when_order_completed(): void
    {
        $order = $this->makeOrder(
            [
                'status'      => Order::STATUS_REVISION_NEEDED,
                'designer_id' => (string) Str::uuid(),
            ],
            ['status' => Order::STATUS_COMPLETED]
        );

        $this->mock(NotificationService::class, function (MockInterface $mock) use ($order) {
            $mock->shouldReceive('orderCompleted')
                ->once()
                ->with(\Mockery::on(fn ($o) => $o === $order));
        });

        (new OrderObserver())->updated($order);
    }

    public function test_sends_no_notification_when_status_and_designer_unchanged(): void
    {
        $order = $this->makeOrder(
            [
                'status'      => Order::STATUS_REVISION_NEEDED,
                'designer_id' => (string) Str::uuid(),
            ],
            ['updated_at' => now()]   // hanya timestamp yang berubah
        );

        $this->mock(NotificationService::class, function (MockInterface $mock) {
            $mock->shouldNotReceive('orderTaken');
            $mock->shouldNotReceive('newOrderAvailable');
            $mock->shouldNotReceive('orderCompleted');
            $mock->shouldNotReceive('revisionRequested');
            $mock->shouldNotReceive('systemActivity');
        });

        (new OrderObserver())->updated($order);
    }
}