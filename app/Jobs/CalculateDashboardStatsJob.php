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

        // 1. Data Operasional Real-time & Verifikasi Pembayaran
        $pendingPayments   = Payment::where('status', Payment::STATUS_PENDING)->count();
        $paidOrdersMonthly = Payment::where('status', Payment::STATUS_BERHASIL)
            ->where('type', Payment::TYPE_ORDER)
            ->whereYear('paid_at', $now->year)
            ->whereMonth('paid_at', $now->month)
            ->count();

        $unpaidExpired = Order::kedaluwarsaPembayaran()->count();
        $unpaidActive  = Order::menungguPembayaran()->count();

        // Pemisahan Order Berjalan:
        // A. Mencari Desainer (Order lunas tapi belum ada desainer)
        $waitingDesignerOrders = Order::whereIn('status', [
            Order::STATUS_PENDING,
            Order::STATUS_WAITING_DESIGNER
        ])->count();

        // B. Sedang Dikerjakan (Desainer sudah ada & dalam proses pengerjaan/revisi/review)
        $inProgressOrders = Order::whereIn('status', [
            Order::STATUS_IN_PROGRESS,
            Order::STATUS_REVISION_NEEDED,
            Order::STATUS_DELIVERABLE_SENT
        ])->count();

        // 2. Data Performa KHUSUS BULAN INI
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

        // 3. Data Rekapitulasi ALL-TIME
        $totalOrdersAllTime  = Order::count();
        $totalRevenueAllTime = Payment::where('status', Payment::STATUS_BERHASIL)->sum('amount');

        $stats = [
            'pending_payments'        => $pendingPayments,
            'paid_orders_monthly'     => $paidOrdersMonthly,
            'unpaid_expired'          => $unpaidExpired,
            'unpaid_active'           => $unpaidActive,
            'waiting_designer_orders' => $waitingDesignerOrders, // Kartu Baru
            'in_progress_orders'      => $inProgressOrders,      // Kartu Baru
            'completed_orders_monthly' => $completedOrdersMonthly,
            'designer_avg_rating'     => number_format($avgRatingMonthly, 1),
            'monthly_revenue'         => $monthlyRevenue,
            'total_orders_all_time'   => $totalOrdersAllTime,
            'total_revenue_all_time'  => $totalRevenueAllTime,
            'last_updated'            => $now->translatedFormat('H:i'),
        ];

        Cache::put('admin_dashboard_stats', $stats, now()->addHours(2));
    }
}