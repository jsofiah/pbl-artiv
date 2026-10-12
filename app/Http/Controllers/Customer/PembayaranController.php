<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use App\Services\R2StorageService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class PembayaranController extends Controller
{
    protected R2StorageService $r2Storage;

    public function __construct(R2StorageService $r2Storage)
    {
        $this->r2Storage = $r2Storage;
    }

    /**
     * Customer mengunggah bukti pembayaran (untuk order baru atau revision_fee).
     */
    public function store(Request $request, Order $order)
    {
        if ($order->customer_id !== Auth::id()) {
            abort(403);
        }

        if ($order->created_at->addDay()->isPast()) {

            // $order->update(['status' => 'gagal']); 

            return back()->withErrors([
                'error' => 'Maaf, waktu pembayaran telah kadaluwarsa (lebih dari 1 hari).'
            ]);
        }

        $request->validate([
            'method' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0',
            'type'   => 'sometimes|in:' . Payment::TYPE_ORDER . ',' . Payment::TYPE_REVISION_FEE,
            'proof'  => 'required|file|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $proofPath = null;
        $storage = $this->r2Storage;

        DB::beginTransaction();
        try {
            $proofPath = $storage->uploadPrivate($request->file('proof'), 'payment-proofs');

            $payment = Payment::create([
                'order_id'   => $order->id,
                'type'       => $request->input('type', Payment::TYPE_ORDER),
                'method'     => $request->method,
                'amount'     => $request->amount,
                'proof_url'  => $proofPath,
                'status'     => Payment::STATUS_PENDING,
                'paid_at'    => now(),
            ]);

            DB::commit();

            return redirect()->route('customer.pemesanan.sukses', $order->id)
                ->with('success', 'Bukti pembayaran berhasil diunggah dan sedang menunggu verifikasi admin.');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($proofPath) {
                try {
                    $storage->deletePrivate($proofPath);
                } catch (\Exception $ex) {
                    Log::error('Gagal menghapus file R2 saat rollback pembayaran: ' . $ex->getMessage());
                }
            }

            Log::error('Gagal memproses pembayaran: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Gagal memproses pembayaran: ' . $e->getMessage()])->withInput();
        }
    }
    /**
     * Admin memverifikasi pembayaran (buat nanti waktu udah ada).
     */
    // public function updateStatus(Request $request, Payment $payment)
    // {
    //     $request->validate([
    //         'status' => 'required|in:' . Payment::STATUS_BERHASIL . ',' . Payment::STATUS_GAGAL,
    //     ]);

    //     return DB::transaction(function () use ($request, $payment) {
    //         $newStatus = $request->status;

    //         if ($newStatus === Payment::STATUS_BERHASIL) {
    //             $payment->markAsSuccess(); 
    //         } else {
    //             $payment->update([
    //                 'status'  => Payment::STATUS_GAGAL,
    //                 'paid_at' => null,
    //             ]);
    //         }

    //         // Jika pembayaran utama order berhasil diverifikasi, ubah status order menjadi 'in_progress'
    //         if ($payment->isSuccess() && $payment->isOrderPayment()) {
    //             $order = $payment->order;
    //             if ($order && $order->isPending()) { 
    //                 $order->update([
    //                     'status' => Order::STATUS_IN_PROGRESS,
    //                     'assigned_at' => $order->assigned_at ?? now(),
    //                 ]);
    //             }
    //         }

    //         return response()->json([
    //             'message' => "Status pembayaran berhasil diperbarui menjadi {$newStatus}.",
    //             'data'    => $payment
    //         ]);
    //     });
    // }

    /**
     * Menampilkan riwayat pembayaran berdasarkan order.
     */
    public function showByOrder(Order $order)
    {
        $payments = $order->payments()->latest()->get()->map(function ($payment) {
            if ($payment->proof_url) {
                $payment->proof_url = $this->r2Storage->temporaryUrlPrivate($payment->proof_url);
            }
            return $payment;
        });

        return view('customer.pemesanan.pembayaran', compact('order'));
    }

    /**
     * Menampilkan halaman status menunggu verifikasi pembayaran.
     */
    public function success(Order $order)
    {
        if ($order->customer_id !== Auth::id()) {
            abort(403);
        }

        if (!$order->hasUploadedPayment()) {
            return redirect()->route('customer.pemesanan.pembayaran', $order->id);
        }

        $payment = $order->payments()->latest()->first();

        return view('customer.pemesanan.sukses', compact('order', 'payment'));
    }
}
