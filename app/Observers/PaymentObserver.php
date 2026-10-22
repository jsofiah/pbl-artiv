<?php

namespace App\Observers;

use App\Models\Payment;
use App\Services\NotificationService;

class PaymentObserver
{
    // Customer mengunggah bukti bayar -> admin diberi tahu
    public function created(Payment $payment): void
    {
        app(NotificationService::class)->paymentReceived($payment);
    }

    // Admin menyetujui pembayaran order -> order masuk Job Pool -> designer diberi tahu
    public function updated(Payment $payment): void
    {
        if ($payment->wasChanged('status') && $payment->isSuccess() && $payment->isOrderPayment()) {
            $order = $payment->order;

            if ($order && !$order->designer_id) {
                app(NotificationService::class)->newOrderAvailable($order);
            }
        }
    }
}