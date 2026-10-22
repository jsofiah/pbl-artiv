<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Jobs\ExportDashboardReportJob;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Menampilkan Halaman Utama Dashboard Admin
     */
    public function index()
    {
        // 1. Data 6 Card Statistik Ringkas Top Bar
        $stats = [
            'total_orders'     => Order::count(),
            'active_orders'    => Order::whereIn('status', [
                'pending', 'waiting_designer', 'in_progress', 'revision_needed', 'deliverable_sent'
            ])->count(),
            'completed_orders' => Order::where('status', Order::STATUS_COMPLETED)->count(),
            'total_revenue'    => Payment::where('status', Payment::STATUS_BERHASIL)->sum('amount'),
            'active_customers' => User::where('role', 'customer')->count(),
            'active_designers' => User::where('role', 'designer')->count(),
        ];

        // 2. Transaksi Terbaru (5 Data Terakhir)
        $recentOrders = Order::with(['product', 'customer', 'productTier'])
            ->latest()
            ->take(5)
            ->get();

        // 3. Distribusi Kategori
        $categoryDistribution = [
            'Branding & Logo'   => 45,
            'UI/UX Design'      => 30,
            'Ilustrasi Digital' => 15,
            'Lainnya'           => 10,
        ];

        // 4. Total Pendapatan Bulan Ini
        $monthlyRevenue = Payment::where('status', Payment::STATUS_BERHASIL)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        return view('admin.dashboard', compact(
            'stats',
            'recentOrders',
            'categoryDistribution',
            'monthlyRevenue'
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