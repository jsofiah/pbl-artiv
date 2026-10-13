<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderLog;
use App\Models\OrderReference;
use App\Models\Payment;
use App\Models\Product;
use App\Services\R2StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class PembayaranController extends Controller
{
    protected R2StorageService $r2Storage;

    public function __construct(R2StorageService $r2Storage)
    {
        $this->r2Storage = $r2Storage;
    }

    public function show(Product $product)
    {
        $draft = session('pemesanan_draft');

        if (!$draft || ($draft['product_id'] ?? null) !== $product->id) {
            return redirect()
                ->route('customer.pemesanan.create', $product->id)
                ->withErrors(['error' => 'Sesi pemesanan telah berakhir. Silakan isi ulang.']);
        }

        $order = new Order([
            'order_code'  => $draft['order_code'],
            'total_price' => $draft['data']['total_price'],
        ]);

        return view('customer.pemesanan.pembayaran', [
            'product' => $product,
            'order'   => $order,
            'draft'   => $draft,
        ]);
    }

    public function store(Request $request, Product $product)
    {
        $draft = session('pemesanan_draft');

        if (!$draft || ($draft['product_id'] ?? null) !== $product->id) {
            return redirect()
                ->route('customer.pemesanan.create', $product->id)
                ->withErrors(['error' => 'Sesi pemesanan telah berakhir. Silakan isi ulang.']);
        }

        $request->validate([
            'method' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0',
            'type'   => 'sometimes|in:' . Payment::TYPE_ORDER . ',' . Payment::TYPE_REVISION_FEE,
            'proof'  => 'required|file|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'proof.required' => 'Bukti pembayaran wajib diunggah.',
            'proof.image'    => 'File harus berupa gambar.',
            'proof.mimes'    => 'Format bukti harus JPG, JPEG, atau PNG.',
            'proof.max'      => 'Ukuran bukti maksimal 2MB.',
        ]);

        $pemesananData = $draft['data'];
        $references    = $draft['references'] ?? [];

        $proofPath = null;

        DB::beginTransaction();
        try {
            $proofPath = $this->r2Storage->uploadPrivate(
                $request->file('proof'),
                'payment-proofs'
            );

            $movedReferences = [];
            foreach ($references as $ref) {
                if (($ref['type'] ?? null) === 'file' && !empty($ref['file_url'])) {
                    $oldPath = $ref['file_url'];
                    $newPath = str_replace('temp/references/', 'references/', $oldPath);

                    if ($this->r2Storage->movePrivate($oldPath, $newPath)) {
                        $ref['file_url'] = $newPath;
                    } else {
                        Log::warning("Gagal pindah file referensi: {$oldPath} → {$newPath}");
                    }
                }
                $movedReferences[] = $ref;
            }

            $order = Order::create([
                'order_code'      => $draft['order_code'],
                'customer_id'     => Auth::id(),
                'designer_id'     => null,
                'product_id'      => $product->id,
                'product_tier_id' => $pemesananData['product_tier_id'],
                'unit_price'      => $pemesananData['unit_price'],
                'quantity'        => $pemesananData['quantity'],
                'deadline'        => $pemesananData['deadline'],
                'is_express'      => $pemesananData['is_express'],
                'express_fee_id'  => $pemesananData['express_fee_id'],
                'express_fee'     => $pemesananData['express_fee'],
                'brief_note'      => $pemesananData['brief_note'],
                'total_price'     => $pemesananData['total_price'],
                'status'          => Order::STATUS_PENDING,
            ]);

            foreach ($movedReferences as $ref) {
                OrderReference::create([
                    'order_id'     => $order->id,
                    'type'         => $ref['type'],
                    'file_url'     => $ref['file_url'] ?? null,
                    'file_name'    => $ref['file_name'] ?? null,
                    'file_size'    => $ref['file_size'] ?? null,
                    'mime_type'    => $ref['mime_type'] ?? null,
                    'external_url' => $ref['external_url'] ?? null,
                ]);
            }

            Payment::create([
                'order_id'  => $order->id,
                'type'      => $request->input('type', Payment::TYPE_ORDER),
                'method'    => $request->input('method'),
                'amount'    => $request->input('amount'),
                'proof_url' => $proofPath,
                'status'    => Payment::STATUS_PENDING,
                'paid_at'   => now(),
            ]);

            OrderLog::create([
                'order_id' => $order->id,
                'actor_id' => Auth::id(),
                'action'   => OrderLog::ACTION_CREATE,
                'status'   => Order::STATUS_PENDING,
                'note'     => 'Order dibuat oleh customer dan menunggu designer mengambil pekerjaan.',
            ]);

            session()->forget([
                'pemesanan_data',
                'pemesanan_references',
                'pemesanan_draft',
            ]);

            DB::commit();

            return redirect()
                ->route('customer.pemesanan.sukses', $order->id)
                ->with('success', 'Pesanan berhasil dibuat. Menunggu verifikasi pembayaran.');

        } catch (\Exception $e) {
            DB::rollBack();

            if ($proofPath) {
                try {
                    $this->r2Storage->deletePrivate($proofPath);
                } catch (\Exception $ex) {
                    Log::error('Gagal hapus bukti R2 saat rollback: ' . $ex->getMessage());
                }
            }

            Log::error('Gagal memproses pembayaran: ' . $e->getMessage());
            return back()
                ->withErrors(['error' => 'Gagal memproses pembayaran: ' . $e->getMessage()])
                ->withInput();
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

    public function success(Order $order)
    {
        if ($order->customer_id !== Auth::id()) {
            abort(403);
        }

        $payment = $order->payments()->latest()->first();

        return view('customer.pemesanan.sukses', compact('order', 'payment'));
    }
}
