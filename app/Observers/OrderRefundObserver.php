<?php

namespace App\Observers;

use App\Models\OrderRefund;
use App\Services\NotificationService;

class OrderRefundObserver
{
    public function created(OrderRefund $refund): void
    {
        app(NotificationService::class)->refundRequested($refund);
    }
}