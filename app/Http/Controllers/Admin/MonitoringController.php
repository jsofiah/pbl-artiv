<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\R2StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\OrderRefund;
use App\Models\OrderLog;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

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
                'pending', 'waiting_designer', 'in_progress', 'revision_needed', 'deliverable_sent'
            ])->count(),
            'completed_orders'  => Order::where('status', Order::STATUS_COMPLETED)->count(),
            'late_orders'       => Order::whereIn('status', [
                'pending', 'waiting_designer', 'in_progress', 'revision_needed', 'deliverable_sent'
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

        // Paginate 10 Data per Halaman + Pertahankan Query String URL
        $orders = $query->paginate(10)->withQueryString();

        // Best Practice Data Verifikasi Pembayaran:
        // Hanya tarik status PENDING atau riwayat 30 hari terakhir dengan hard limit 100 data
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
            }

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

            DB::commit();

            return back()->with('warning', "Pembayaran untuk Order {$payment->order->order_code} telah ditolak.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Gagal Tolak Pembayaran: ' . $e->getMessage());
            return back()->with('error', 'Gagal menolak pembayaran.');
        }
    }

    public function selesaikanRefund(Request $request, OrderRefund $refund)
    {
        if ($refund->isCompleted()) {
            return back()->with('error', 'Refund ini sudah selesai diproses.');
        }

        $request->validate([
            'proof' => 'required|file|mimes:png,jpg,jpeg,webp,pdf|max:5120',
        ], [
            'proof.required' => 'Bukti transfer refund wajib diunggah.',
            'proof.mimes'    => 'Format file harus PNG, JPG, WEBP, atau PDF.',
            'proof.max'      => 'Ukuran bukti maksimal 5 MB.',
        ]);

        DB::beginTransaction();
        try {
            /** @var R2StorageService $storage */
            $storage = app(R2StorageService::class);
            $proofPath = $storage->upload($request->file('proof'), 'refund-proofs');

            $refund->markAsCompleted(Auth::user(), $proofPath);

            $order = $refund->order;
            $order->update(['status' => Order::STATUS_REFUNDED]);

            OrderLog::create([
                'order_id' => $order->id,
                'actor_id' => Auth::id(),
                'action'   => OrderLog::ACTION_REFUND_COMPLETED,
                'status'   => Order::STATUS_REFUNDED,
                'note'     => 'Admin telah memproses pengembalian dana ke customer beserta bukti transfer.',
            ]);

            Notification::create([
                'user_id' => $order->customer_id,
                'type'    => Notification::TYPE_REFUND_COMPLETED,
                'title'   => 'Refund Selesai',
                'body'    => "Dana refund untuk order {$order->order_code} telah ditransfer. Silakan cek bukti transfer di halaman pesanan.",
                'data'    => ['url' => route('customer.pesanan.show', $order->id)],
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Gagal proses refund: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses refund: ' . $e->getMessage());
        }

        return back()->with('success', "Refund order {$refund->order->order_code} berhasil diselesaikan.");
    }
}