<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use App\Jobs\ExportDashboardReportJob;
use Illuminate\Http\Request;
use App\Jobs\CalculateDashboardStatsJob;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Menampilkan Halaman Utama Dashboard Admin
     */
    public function index()
    {
        // 1. Ambil data statistik dari Cache (atau hitung via Job jika cache kosong)
        $stats = Cache::get('admin_dashboard_stats');
        if (! $stats) {
            CalculateDashboardStatsJob::dispatchSync();
            $stats = Cache::get('admin_dashboard_stats');
        }

        // 2. Query Pendapatan Harian untuk ApexCharts (Bulan Berjalan)
        $now = now();
        $daysInMonth = $now->daysInMonth;

        $dailyPayments = Payment::where('status', Payment::STATUS_BERHASIL)
            ->whereYear('paid_at', $now->year)
            ->whereMonth('paid_at', $now->month)
            ->selectRaw('DATE(paid_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        $chartDates = [];
        $chartRevenues = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($now->year, $now->month, $day);
            $dateKey = $date->format('Y-m-d');

            $chartDates[] = $date->format('d M');
            $chartRevenues[] = (float) ($dailyPayments[$dateKey] ?? 0);
        }

        // 3. Query Pendapatan Bulan Ini
        $monthlyRevenue = Payment::where('status', Payment::STATUS_BERHASIL)
            ->whereYear('paid_at', $now->year)
            ->whereMonth('paid_at', $now->month)
            ->sum('amount');

        // 4. Query 5 Transaksi Terbaru untuk Tabel
        $recentOrders = Order::with(['customer', 'product'])
            ->latest()
            ->take(5)
            ->get();

        // 5. Data Distribusi Kategori
        // Query Distribusi Kategori Jasa (Horizontal Bar Chart)
        $totalOrders = Order::count();
        $products = Product::withCount('orders')
            ->orderByDesc('orders_count') // Urutkan dari yang paling banyak dipesan
            ->get();

        // Ambil jumlah order per kategori produk
        $categoryData = DB::table('products')
            ->leftJoin('orders', 'products.id', '=', 'orders.product_id')
            ->select('products.name', DB::raw('COUNT(orders.id) as total'))
            ->groupBy('products.id', 'products.name')
            ->get();

        $categoryNames  = $categoryData->pluck('name')->toArray();
        $categoryCounts = $categoryData->pluck('total')->toArray();

        // 1. Data Operasional Real-time & Verifikasi Pembayaran
        $pendingPayments   = Payment::where('status', Payment::STATUS_PENDING)->count();
        $paidOrdersMonthly = Payment::where('status', Payment::STATUS_BERHASIL)
            ->whereYear('paid_at', $now->year)
            ->whereMonth('paid_at', $now->month)
            ->count(); // Pesanan yang sudah dibayar & terverifikasi bulan ini

        $unpaidExpired     = Order::kedaluwarsaPembayaran()->count();
        $unpaidActive      = Order::menungguPembayaran()->count();
        $activeOrders      = Order::whereIn('status', Order::AKTIF)->count();

        return view('admin.dashboard', compact(
            'stats',                 // $stats ini sudah mencakup $stats['paid_orders_monthly']
            'chartDates',
            'chartRevenues',
            'monthlyRevenue',
            'recentOrders',
            'categoryNames',
            'categoryCounts'
        ));
    }

    /**
     * Menangani Request Ekspor Laporan via Queue Worker
     */
    public function exportReport(Request $request)
    {
        $user = $request->user() ?? Auth::user();
        // Kirim job ke antrean background
        ExportDashboardReportJob::dispatch(
            $user,
            $request->input('start_date'),
            $request->input('end_date')
        );

        return back()->with('success', 'Ekspor laporan lagi diproses di background sama Queue Worker!');
    }
}
