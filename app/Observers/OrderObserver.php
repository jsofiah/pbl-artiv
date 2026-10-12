<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\NotificationService;

class OrderObserver
{
    public function updated(Order $order): void
    {
        if ($order->wasChanged('status') && $order->status === 'completed') {
            app(NotificationService::class)->orderCompleted($order);
        }
    }
}