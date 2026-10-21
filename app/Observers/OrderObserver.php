<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\DesignerStat;
use App\Models\OrderSplit;
use App\Models\Review;
use App\Services\NotificationService;

class OrderObserver
{
    public function updated(Order $order): void
    {
        // ============ 1. NOTIFIKASI (existing) ============
        if ($order->wasChanged('status') && $order->status === 'completed') {
            app(NotificationService::class)->orderCompleted($order);
        }

        if ($order->wasChanged(['status', 'designer_id', 'total_price', 'reassign_count'])) {
            if ($order->designer_id) {
                $this->recalculateStats($order->designer_id);
            }

            if ($order->wasChanged('designer_id')) {
                $oldDesignerId = $order->getOriginal('designer_id');
                if ($oldDesignerId) {
                    $this->recalculateStats($oldDesignerId);
                }
            }
        }
    }

    public function deleted(Order $order): void
    {
        if ($order->designer_id) {
            $this->recalculateStats($order->designer_id);
        }
    }

    private function recalculateStats(string $designerId): void
    {
        $totalOrders     = Order::where('designer_id', $designerId)->count();
        $completedOrders = Order::where('designer_id', $designerId)
                                ->where('status', Order::STATUS_COMPLETED)
                                ->count();
        $totalEarning    = OrderSplit::where('designer_id', $designerId)
                                ->sum('designer_earning');
        $avgRating       = Review::where('designer_id', $designerId)->avg('rating') ?? 0;

        DesignerStat::updateOrCreate(
            ['designer_id' => $designerId],
            [
                'total_orders'     => $totalOrders,
                'completed_orders' => $completedOrders,
                'average_rating'   => round((float) $avgRating, 2),
                'total_earning'    => $totalEarning,
            ]
        );
    }
}