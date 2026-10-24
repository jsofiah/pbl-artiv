<?php

namespace App\Jobs;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Review;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class CalculateDashboardStatsJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $now = now();

        $pendingPayments   = Payment::where('status', Payment::STATUS_PENDING)->count();
        $paidOrdersMonthly = Payment::where('status', Payment::STATUS_BERHASIL)
            ->whereYear('paid_at', $now->year)
            ->whereMonth('paid_at', $now->month)
            ->count();

        $unpaidExpired     = Order::kedaluwarsaPembayaran()->count();
        $unpaidActive      = Order::menungguPembayaran()->count();
        $activeOrders      = Order::whereIn('status', Order::AKTIF)->count();

        $completedOrdersMonthly = Order::where('status', Order::STATUS_COMPLETED)
            ->whereYear('completed_at', $now->year)
            ->whereMonth('completed_at', $now->month)
            ->count();

        $avgRatingMonthly = Review::whereYear('created_at', $now->year)
            ->whereMonth('created_at', $now->month)
            ->avg('rating');

        if (is_null($avgRatingMonthly)) {
            $avgRatingMonthly = Review::avg('rating') ?? 0;
        }

        $monthlyRevenue = Payment::where('status', Payment::STATUS_BERHASIL)
            ->whereYear('paid_at', $now->year)
            ->whereMonth('paid_at', $now->month)
            ->sum('amount');

        $totalOrdersAllTime  = Order::count();
        $totalRevenueAllTime = Payment::where('status', Payment::STATUS_BERHASIL)->sum('amount');

        $stats = [
            'pending_payments'         => $pendingPayments,
            'paid_orders_monthly'      => $paidOrdersMonthly,
            'unpaid_expired'           => $unpaidExpired,
            'unpaid_active'            => $unpaidActive,
            'active_orders'            => $activeOrders,
            'completed_orders_monthly' => $completedOrdersMonthly,
            'designer_avg_rating'      => number_format($avgRatingMonthly, 1),
            'monthly_revenue'          => $monthlyRevenue,
            'total_orders_all_time'    => $totalOrdersAllTime,
            'total_revenue_all_time'   => $totalRevenueAllTime,
            'last_updated'             => $now->translatedFormat('H:i'),
        ];

        Cache::put('admin_dashboard_stats', $stats, now()->addHours(2));
    }
}