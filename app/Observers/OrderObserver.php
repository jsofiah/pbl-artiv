<?php

namespace App\Observers;

use App\Models\Notification;
use App\Models\Order;
use App\Services\NotificationService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

class OrderObserver implements ShouldHandleEventsAfterCommit
{
    public function updated(Order $order): void
    {
        $service = app(NotificationService::class);

        // Designer mengambil order dari Job Pool -> customer diberi tahu
        if ($order->wasChanged('designer_id') && $order->designer_id) {
            $service->orderTaken($order);
        }

        if (!$order->wasChanged('status')) {
            return;
        }

        match ($order->status) {
            Order::STATUS_WAITING_DESIGNER => $order->designer_id ? null : $service->newOrderAvailable($order),

            Order::STATUS_COMPLETED        => $service->orderCompleted($order),

            Order::STATUS_REVISION_NEEDED  => $service->revisionRequested($order),

            Order::STATUS_WAITING_CUSTOMER_DECISION => $service->systemActivity(
                Notification::TYPE_CANNOT_CONTINUE,
                'Designer tidak dapat melanjutkan',
                "Pesanan {$order->order_code} menunggu keputusan customer."
            ),

            Order::STATUS_CANCELLED        => $service->systemActivity(
                Notification::TYPE_SYSTEM,
                'Pesanan dibatalkan',
                "Pesanan {$order->order_code} dibatalkan."
            ),

            default => null,
        };
    }
}