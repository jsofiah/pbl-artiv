<?php

namespace App\Http\Controllers\Designer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\DesignerStat;
use App\Models\OrderSplit;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $designerId = Auth::id();

        $stats = DesignerStat::where('designer_id', $designerId)->first();

        $statistik = [
            'order_aktif'      => Order::where('designer_id', $designerId)
                                        ->whereIn('status', [
                                            Order::STATUS_IN_PROGRESS,
                                            Order::STATUS_DELIVERABLE_SENT,
                                            Order::STATUS_REVISION_NEEDED,
                                            Order::STATUS_WAITING_CUSTOMER_DECISION,
                                        ])
                                        ->count(),
            'order_selesai'    => $stats?->completed_orders ?? 0,
            'rating'           => (float) ($stats?->average_rating ?? 0),
            'jumlah_ulasan'    => $stats?->completed_orders ?? 0,
            'total_pendapatan' => (float) ($stats?->total_earning ?? 0),
        ];

        $orderAktif = Order::with(['product', 'productTier', 'customer'])
            ->where('designer_id', $designerId)
            ->whereIn('status', [
                Order::STATUS_IN_PROGRESS,
                Order::STATUS_DELIVERABLE_SENT,
                Order::STATUS_REVISION_NEEDED,
                Order::STATUS_WAITING_CUSTOMER_DECISION,
            ])
            ->orderByDesc('created_at')
            ->take(3)
            ->get();

        $pekerjaanTerbaru = Order::with(['product', 'productTier', 'customer'])
            ->inJobPool()
            ->orderByDesc('created_at')
            ->take(3)
            ->get();

        // ============ PENDAPATAN BULANAN ============
        $bulanIni = OrderSplit::where('designer_id', $designerId)
            ->whereHas('order', function ($q) {
                $q->where('status', Order::STATUS_COMPLETED)
                ->whereMonth('completed_at', now()->month)
                ->whereYear('completed_at', now()->year);
            })
            ->sum('designer_earning');

        // Total earning (NET) bulan lalu
        $bulanLalu = OrderSplit::where('designer_id', $designerId)
            ->whereHas('order', function ($q) {
                $q->where('status', Order::STATUS_COMPLETED)
                ->whereMonth('completed_at', now()->subMonthNoOverflow()->month)
                ->whereYear('completed_at', now()->subMonthNoOverflow()->year);
            })
            ->sum('designer_earning');

        $target = max(5_000_000, round((float) $bulanLalu * 1.1, -5));

        $pendapatan = [
            'bulan_ini'  => (float) $bulanIni,
            'bulan_lalu' => (float) $bulanLalu,
            'target'     => (float) $target,
        ];

        $persenBulanIni  = $target > 0 ? (int) min(100, round($bulanIni / $target * 100)) : 0;
        $persenBulanLalu = $target > 0 ? (int) min(100, round($bulanLalu / $target * 100)) : 0;

        $notifikasi = Order::with(['product'])
            ->where('designer_id', $designerId)
            ->orderByDesc('updated_at')
            ->take(4)
            ->get()
            ->map(fn ($order) => (object) [
                'judul'    => $this->labelAktivitas($order->status),
                'isi'      => $order->product?->name ?? 'Pekerjaan',
                'waktu'    => $order->updated_at->diffForHumans(),
                'order_id' => $order->id,
            ]);

        $trenBulanan = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonthsNoOverflow($i);
            $total = \App\Models\OrderSplit::where('designer_id', $designerId)
                ->whereHas('order', function ($q) use ($date) {
                    $q->where('status', Order::STATUS_COMPLETED)
                    ->whereMonth('completed_at', $date->month)
                    ->whereYear('completed_at', $date->year);
                })
                ->sum('designer_earning');

            $trenBulanan[] = [
                'bulan' => $date->translatedFormat('M Y'),
                'total' => (float) $total,
            ];
        }

        $statusBreakdown = [
            'in_progress'      => $orderAktif->where('status', Order::STATUS_IN_PROGRESS)->count(),
            'deliverable_sent' => $orderAktif->where('status', Order::STATUS_DELIVERABLE_SENT)->count(),
            'revision_needed'  => $orderAktif->where('status', Order::STATUS_REVISION_NEEDED)->count(),
            'waiting_decision' => $orderAktif->where('status', Order::STATUS_WAITING_CUSTOMER_DECISION)->count(),
        ];

        $kapasitasMax     = $stats?->max_active_orders ?? 5;
        $kapasitasTerpakai = $statistik['order_aktif'];
        $persenKapasitas  = $kapasitasMax > 0
            ? (int) min(100, round($kapasitasTerpakai / $kapasitasMax * 100))
            : 0;

        $isAvailable = ($stats?->availability_status ?? 'available') === 'available';
        $isSuspended = $stats?->suspended_until && $stats->suspended_until->isFuture();

        $jobPoolTersedia = Order::inJobPool()->count();

        return view('designer.dashboard', [
            'stats'                 => $stats,  
            'statistik'             => $statistik,
            'orderAktif'            => $orderAktif,
            'pekerjaanTerbaru'      => $pekerjaanTerbaru,
            'pendapatan'            => $pendapatan,
            'persenBulanIni'        => $persenBulanIni,
            'persenBulanLalu'       => $persenBulanLalu,
            'notifikasi'            => $notifikasi,
            'jobPoolTersedia'       => $jobPoolTersedia,
            'pekerjaanSayaCount'    => $statistik['order_aktif'],
            'notifikasiBelumDibaca' => $notifikasi->count(),
            'trenBulanan'           => $trenBulanan,
            'statusBreakdown' => $statusBreakdown,
            'kapasitasMax'      => $kapasitasMax,
            'kapasitasTerpakai' => $kapasitasTerpakai,
            'persenKapasitas'   => $persenKapasitas,
            'isAvailable'       => $isAvailable,
            'isSuspended'       => $isSuspended,
        ]);

    }

    public function jobPool(): View
    {
        $orders = Order::with(['product', 'productTier', 'customer'])
            ->inJobPool()
            ->orderByDesc('created_at')
            ->paginate(12);

        $jobPoolTersedia = $orders->total();

        return view('designer.job-pool', compact('orders', 'jobPoolTersedia'));
    }

    public function notifikasi(): View
    {
        $designerId = Auth::id();

        $notifikasi = Order::with(['product'])
            ->where('designer_id', $designerId)
            ->orderByDesc('updated_at')
            ->take(20)
            ->get()
            ->map(fn ($order) => (object) [
                'judul'    => $this->labelAktivitas($order->status),
                'isi'      => $order->product?->name ?? 'Pekerjaan',
                'waktu'    => $order->updated_at->diffForHumans(),
                'order_id' => $order->id,
            ]);

        return view('designer.notifikasi', compact('notifikasi'));
    }

    public function riwayat(): View
    {
        $designerId = Auth::id();

        $orders = Order::with(['product', 'productTier', 'customer'])
            ->where('designer_id', $designerId)
            ->riwayat()
            ->orderByDesc('completed_at')
            ->paginate(12);

        return view('designer.riwayat', compact('orders'));
    }


    private function labelAktivitas(string $status): string
    {
        return match ($status) {
            Order::STATUS_PENDING                   => 'Pekerjaan baru menunggu',
            Order::STATUS_WAITING_DESIGNER          => 'Pekerjaan baru di pool',
            Order::STATUS_IN_PROGRESS               => 'Sedang dikerjakan',
            Order::STATUS_DELIVERABLE_SENT          => 'Menunggu review customer',
            Order::STATUS_REVISION_NEEDED           => 'Revisi baru dari customer',
            Order::STATUS_WAITING_CUSTOMER_DECISION => 'Menunggu keputusan customer',
            Order::STATUS_COMPLETED                 => 'Pekerjaan selesai',
            Order::STATUS_CANCELLED                 => 'Pekerjaan dibatalkan',
            Order::STATUS_REFUND_REQUESTED          => 'Customer ajukan refund',
            Order::STATUS_REFUNDED                  => 'Refund selesai',
            default                                 => 'Update pekerjaan',
        };
    }

    public function toggleAvailability(): \Illuminate\Http\RedirectResponse
    {
        $designerId = Auth::id();
        $stats = DesignerStat::where('designer_id', $designerId)->first();

        if (!$stats) {
            return back()->withErrors(['availability' => 'Data statistik tidak ditemukan.']);
        }

        if ($stats->suspended_until && $stats->suspended_until->isFuture()) {
            return back()->withErrors(['availability' => 'Anda sedang di-suspend, tidak bisa mengubah status.']);
        }

        $stats->update([
            'availability_status' => $stats->availability_status === 'available'
                ? 'unavailable'
                : 'available',
        ]);

        return back()->with('status', 'Status availability diperbarui.');
    }
}