<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\R2StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\OrderRefund;
use App\Models\OrderLog;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;


class MonitoringController extends Controller
{
    protected R2StorageService $r2Storage;

    public function __construct(R2StorageService $r2Storage)
    {
        $this->r2Storage = $r2Storage;
    }

    /**
     * Halaman Utama Monitoring (Progress Pekerjaan & Verifikasi Pembayaran)
     */
    public function index(Request $request)
    {
        // Card Statistik Ringkas Top Bar
        $stats = [
            'pending_payments' => Payment::where('status', Payment::STATUS_PENDING)->count(),
            'active_orders'     => Order::whereIn('status', [
                'pending',
                'waiting_designer',
                'in_progress',
                'revision_needed',
                'deliverable_sent'
            ])->count(),
            'completed_orders'  => Order::where('status', Order::STATUS_COMPLETED)->count(),
            'late_orders'       => Order::whereIn('status', [
                'pending',
                'waiting_designer',
                'in_progress',
                'revision_needed',
                'deliverable_sent'
            ])->where('deadline', '<', now())->count(),
        ];

        // Query Data Progress Pekerjaan (Server-Side Pagination 10 data per halaman)
        $query = Order::with(['customer', 'designer', 'product', 'productTier']);

        // Filter Pencarian
        if ($request->filled('q')) {
            $search = $request->query('q');
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'ilike', "%{$search}%")
                    ->orWhereHas('customer', function ($c) use ($search) {
                        $c->where('full_name', 'ilike', "%{$search}%")
                            ->orWhere('email', 'ilike', "%{$search}%");
                    })
                    ->orWhereHas('product', function ($p) use ($search) {
                        $p->where('name', 'ilike', "%{$search}%");
                    });
            });
        }

        // Filter Status Order
        if ($request->filled('status_order')) {
            $query->where('status', $request->query('status_order'));
        }

        // Sorting / Urutkan
        $sort = $request->query('sort', 'terbaru');
        if ($sort === 'terlama') {
            $query->oldest();
        } elseif ($sort === 'deadline') {
            $query->orderByRaw('deadline ASC NULLS LAST');
        } else {
            $query->latest();
        }

        // Paginate Data
        $orders = $query->paginate(15)->withQueryString();

        // Hanya status PENDING atau riwayat 30 hari terakhir dengan hard limit 100 data
        $payments = Payment::with(['order.customer', 'order.product'])
            ->where(function ($q) {
                $q->where('status', Payment::STATUS_PENDING)
                    ->orWhere('created_at', '>=', now()->subDays(30));
            })
            ->latest()
            ->limit(100)
            ->get();

        // Transformasi Temporary URL Private R2 Cloudflare
        $payments->transform(function ($payment) {
            if ($payment->proof_url) {
                try {
                    $payment->proof_url_temp = $this->r2Storage->temporaryUrlPrivate($payment->proof_url);
                } catch (\Exception $e) {
                    $payment->proof_url_temp = null;
                    Log::error('Gagal generate temporary URL R2: ' . $e->getMessage());
                }
            }
            return $payment;
        });

        return view('admin.monitoring', compact('stats', 'orders', 'payments'));
    }

    public function detail(Order $order)
    {
        $order->load(['customer', 'designer', 'product', 'productTier', 'logs.actor']);

        // Urutkan riwayat log pengerjaan berdasarkan waktu dibuat
        $logs = $order->logs->sortBy('created_at')->values()->map(function ($log) {
            return [
                'id'         => $log->id,
                'action'     => $log->action,
                'status'     => $log->status,
                'note'       => $log->note ?? 'Tidak ada catatan tambahan.',
                'actor_name' => $log->actor?->full_name ?? 'Sistem',
                'time'       => $log->created_at ? $log->created_at->translatedFormat('d M Y, H:i') . ' WIB' : '-',
            ];
        });

        return response()->json([
            'order_code'    => '#' . $order->order_code,
            'product_name'  => $order->product?->name . ($order->productTier ? ' (' . $order->productTier->name . ')' : ''),
            'customer_name' => $order->customer?->full_name ?? '-',
            'designer_name' => $order->designer?->full_name ?? 'Belum Ada Desainer',
            'deadline'      => $order->deadline ? $order->deadline->translatedFormat('d M Y, H:i') . ' WIB' : '-',
            'updated_at'    => $order->updated_at ? $order->updated_at->translatedFormat('d M Y, H:i') . ' WIB' : '-',
            'status'        => $order->status,
            'status_label'  => Order::STATUS_LABELS[$order->status] ?? $order->status,
            'is_late'       => $order->isDeadlineMissed(),
            'logs'          => $logs,
        ]);
    }

       /**
     * ACC / Approve Pembayaran
     */
    public function approvePayment(Payment $payment)
    {
        if ($payment->isSuccess()) {
            return back()->with('error', 'Pembayaran ini sudah terverifikasi sebelumnya.');
        }

        DB::beginTransaction();
        try {
            $payment->markAsSuccess();

            $order = $payment->order;
            if ($order && $payment->isOrderPayment()) {
                $nextStatus = $order->designer_id
                    ? Order::STATUS_IN_PROGRESS
                    : Order::STATUS_WAITING_DESIGNER;

                $order->update([
                    'status'      => $nextStatus,
                    'assigned_at' => $order->assigned_at ?? now(),
                ]);
            } elseif ($order && $payment->isRevisionFee()) {
                $order->increment('paid_revision_quota');

                OrderLog::create([
                    'order_id' => $order->id,
                    'actor_id' => Auth::id(),
                    'action'   => 'revision_payment_approved',
                    'status'   => $order->status,
                    'note'     => 'Admin menyetujui pembayaran revisi. Customer kini punya 1 kuota revisi tambahan.',
                ]);

                Notification::create([
                    'user_id' => $order->customer_id,
                    'type'    => Notification::TYPE_PAYMENT,
                    'title'   => 'Pembayaran Revisi Disetujui',
                    'body'    => "Pembayaran revisi untuk order {$order->order_code} disetujui. Sekarang Anda bisa mengajukan 1x revisi tambahan.",
                    'data'    => ['url' => route('customer.pesanan.show', $order->id)],
                ]);
            }

            ActivityLog::record(
                'approve_pembayaran',
                "Menyetujui pembayaran {$payment->type} untuk order {$payment->order->order_code}",
                'payment',
                $payment->id
            );

            DB::commit();

            return back()->with('success', "Pembayaran untuk Order {$payment->order->order_code} berhasil di-ACC!");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal ACC Pembayaran: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses verifikasi pembayaran.');
        }
    }

    /**
     * Tolak / Reject Pembayaran
     */
    public function rejectPayment(Payment $payment)
    {
        DB::beginTransaction();
        try {
            $payment->update([
                'status'  => Payment::STATUS_GAGAL,
                'paid_at' => null,
            ]);

            ActivityLog::record(
                'reject_pembayaran',
                "Menolak pembayaran {$payment->type} untuk order {$payment->order->order_code}",
                'payment',
                $payment->id
            );

            DB::commit();

            return back()->with('warning', "Pembayaran untuk Order {$payment->order->order_code} telah ditolak.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal Tolak Pembayaran: ' . $e->getMessage());
            return back()->with('error', 'Gagal menolak pembayaran.');
        }
    }
}
