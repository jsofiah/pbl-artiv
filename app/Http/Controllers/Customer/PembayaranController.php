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

    /**
     * Tampilkan halaman pembayaran (GET).
     * Order BELUM ada di DB — pakai objek Order palsu dari session draft.
     */
    public function show(Product $product)
    {
        $draft = session('pemesanan_draft');

        if (!$draft || ($draft['product_id'] ?? null) !== $product->id) {
            return redirect()
                ->route('customer.pemesanan.create', $product->id)
                ->withErrors(['error' => 'Sesi pemesanan telah berakhir. Silakan isi ulang.']);
        }

        // Objek Order palsu — TIDAK disimpan ke DB.
        // Hanya agar view bisa akses $order->order_code & $order->total_price.
        $order = new Order([
            'order_code'  => $draft['order_code'],
            'total_price' => $draft['data']['total_price'],
        ]);

        return view('customer.pemesanan.pembayaran', [
            'product' => $product,
            'order'   => $order,   // id = null, created_at = null
            'draft'   => $draft,
        ]);
    }

    /**
     * Proses pembayaran (POST) — INTI PERBAIKAN.
     *
     * [Temuan 1] movePrivate: temp/references/ → references/
     * [Temuan 3] OrderLog::create() action=create
     * [Temuan 4] Order::STATUS_PENDING & Payment::STATUS_PENDING
     */
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
            // ────────────────────────────────────────────────────────
            // [1] Upload bukti pembayaran ke R2
            // ────────────────────────────────────────────────────────
            $proofPath = $this->r2Storage->uploadPrivate(
                $request->file('proof'),
                'payment-proofs'
            );

            // ────────────────────────────────────────────────────────
            // [TEMUAN 1] Pindahkan file referensi
            //            temp/references/ → references/
            // ────────────────────────────────────────────────────────
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

            // ────────────────────────────────────────────────────────
            // [TEMUAN 4] Buat Order dengan status PENDING
            // ────────────────────────────────────────────────────────
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
                'status'          => Order::STATUS_PENDING,   // ← TEMUAN 4
            ]);

            // ────────────────────────────────────────────────────────
            // Buat OrderReference (pakai path yang sudah dipindah)
            // ────────────────────────────────────────────────────────
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

            // ────────────────────────────────────────────────────────
            // [TEMUAN 4] Buat Payment dengan status PENDING
            // ────────────────────────────────────────────────────────
            Payment::create([
                'order_id'  => $order->id,
                'type'      => $request->input('type', Payment::TYPE_ORDER),
                'method'    => $request->input('method'),
                'amount'    => $request->input('amount'),
                'proof_url' => $proofPath,
                'status'    => Payment::STATUS_PENDING,   // ← TEMUAN 4
                'paid_at'   => now(),
            ]);

            // ────────────────────────────────────────────────────────
            // [TEMUAN 3] Buat OrderLog pertama (action: create)
            // ────────────────────────────────────────────────────────
            OrderLog::create([
                'order_id' => $order->id,
                'actor_id' => Auth::id(),
                'action'   => OrderLog::ACTION_CREATE,     // 'create'
                'status'   => Order::STATUS_PENDING,       // 'pending'
                'note'     => 'Order dibuat oleh customer dan menunggu designer mengambil pekerjaan.',
            ]);

            // ────────────────────────────────────────────────────────
            // Bersihkan session setelah commit
            // ────────────────────────────────────────────────────────
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

            // Rollback file bukti pembayaran kalau ada
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
     * Halaman sukses — Order sudah ada di DB.
     */
    public function success(Order $order)
    {
        if ($order->customer_id !== Auth::id()) {
            abort(403);
        }

        $payment = $order->payments()->latest()->first();

        return view('customer.pemesanan.sukses', compact('order', 'payment'));
    }

    /**
     * Riwayat pembayaran (opsional).
     */
    public function showByOrder(Order $order)
    {
        $payments = $order->payments()->latest()->get()->map(function ($payment) {
            if ($payment->proof_url) {
                $payment->proof_url = $this->r2Storage->temporaryUrlPrivate($payment->proof_url);
            }
            return $payment;
        });

        return view('customer.pemesanan.pembayaran', compact('order', 'payments'));
    }
}