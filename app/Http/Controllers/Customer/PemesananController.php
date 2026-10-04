<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\ExpressFee;
use App\Models\Order;
use App\Models\OrderReference;
use App\Models\Product;
use App\Models\ProductTier;
use App\Services\R2StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PemesananController extends Controller
{
    public function create(Product $product)
    {
        abort_if(!$product->is_active, 404, 'Jasa tidak tersedia.');

        $product->load(['tiers' => function ($query) {
            $query->where('is_active', true)->orderBy('price');
        }]);

        $expressFees = ExpressFee::where('is_active', true)
            ->get()
            ->sortByDesc(function ($fee) {
                return (int) filter_var($fee->name, FILTER_SANITIZE_NUMBER_INT);
            })
            ->values();

        $defaultTier = $product->tiers->first();
        $expressFeeMap = $expressFees->map(function ($fee) {
            return [
                'id' => $fee->id,
                'name' => $fee->name,
                'days' => (int) filter_var($fee->name, FILTER_SANITIZE_NUMBER_INT),
                'fee' => (float) $fee->fee,
            ];
        })->values()->toJson();

        return view('customer.pemesanan.create', compact('product', 'expressFees', 'defaultTier', 'expressFeeMap'));
    }

    public function ringkasan(Request $request, Product $product)
    {
        $validated = $request->validate([
            'product_tier_id' => 'required|exists:product_tiers,id',
            'quantity' => 'required|integer|min:1',
            'deadline_option' => 'required|in:default,express',
            'target_deadline' => 'nullable|date|after:today',
            'brief_note' => 'nullable|string|max:1000',
            'reference_files.*' => 'nullable|file|max:51200|mimes:png,jpg,jpeg,pdf,ai,psd,zip',
            'reference_links.*' => 'nullable|url',
        ]);

        $tier = ProductTier::where('product_id', $product->id)->findOrFail($validated['product_tier_id']);

        $deadline = now()->addDays(7);
        $expressFee = null;

        if ($validated['deadline_option'] === 'express') {
            if (empty($validated['target_deadline'])) {
                return back()->withErrors(['target_deadline' => 'Tanggal deadline wajib diisi.'])->withInput();
            }

            $deadline = \Carbon\Carbon::parse($validated['target_deadline'])->startOfDay();
            $daysDiff = (int) now()->startOfDay()->diffInDays($deadline, false);

            if ($daysDiff < 1) {
                return back()->withErrors(['target_deadline' => 'Minimal H+1 dari sekarang.'])->withInput();
            }

            $allExpressFees = ExpressFee::where('is_active', true)->get();

            $expressFee = $allExpressFees->first(function ($fee) use ($daysDiff) {
                $days = (int) filter_var($fee->name, FILTER_SANITIZE_NUMBER_INT);
                return $days === $daysDiff;
            });

            if (!$expressFee) {
                $expressFee = $allExpressFees
                    ->sortBy(function ($fee) use ($daysDiff) {
                        $days = (int) filter_var($fee->name, FILTER_SANITIZE_NUMBER_INT);
                        return abs($days - $daysDiff);
                    })
                    ->first();
            }

            if (!$expressFee) {
                return back()->withErrors(['target_deadline' => 'Tidak ada express fee.'])->withInput();
            }
        }

        $unitPrice = $tier->price;
        $quantity = $validated['quantity'];
        $subtotal = $unitPrice * $quantity;
        $expressFeeAmount = $expressFee ? $expressFee->fee : 0;
        $totalPrice = $subtotal + $expressFeeAmount;

        $pemesananData = [
            'product_id' => $product->id,
            'product_tier_id' => $tier->id,
            'product_tier_name' => $tier->name,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
            'express_fee_id' => $expressFee?->id,
            'express_fee_name' => $expressFee?->name,
            'express_fee' => $expressFeeAmount,
            'total_price' => $totalPrice,
            'deadline' => $deadline->toDateTimeString(),
            'brief_note' => $validated['brief_note'] ?? null,
            'is_express' => $validated['deadline_option'] === 'express',
        ];

        session(['pemesanan_data' => $pemesananData]);

        $references = [];
        if ($request->hasFile('reference_files')) {
            $storage = app(R2StorageService::class);
            foreach ($request->file('reference_files') as $file) {
                $path = $storage->upload($file, 'temp/references');
                $references[] = [
                    'type' => 'file',
                    'file_url' => $path,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ];
            }
        }

        if ($request->filled('reference_links')) {
            foreach ($request->reference_links as $link) {
                if (!empty($link)) {
                    $references[] = ['type' => 'link', 'external_url' => $link];
                }
            }
        }

        session(['pemesanan_references' => $references]);

        return view('customer.pembayaran', compact('product', 'pemesananData', 'order'));
    }

    public function showRingkasan(Product $product)
    {
        $pemesananData = session('pemesanan_data');
        $references = session('pemesanan_references', []);

        if (!$pemesananData) {
            return redirect()
                ->route('customer.pemesanan.create', $product->id)
                ->withErrors(['error' => 'Silakan isi form pemesanan terlebih dahulu.']);
        }

        if ($pemesananData['product_id'] !== $product->id) {
            return redirect()
                ->route('customer.pemesanan.create', $product->id)
                ->withErrors(['error' => 'Data pemesanan tidak sesuai.']);
        }

        $tier = ProductTier::find($pemesananData['product_tier_id']);
        $expressFee = $pemesananData['express_fee_id'] ? ExpressFee::find($pemesananData['express_fee_id']) : null;

        return view('customer.pemesanan.ringkasan', compact('product', 'pemesananData', 'tier', 'expressFee', 'references'));
    }

    public function konfirmasi(Request $request, Product $product)
    {
        $pemesananData = session('pemesanan_data');
        $references = session('pemesanan_references', []);

        if (!$pemesananData) {
            return redirect()
                ->route('customer.pemesanan.create', $product->id)
                ->withErrors(['error' => 'Sesi pemesanan telah berakhir. Silakan isi ulang.']);
        }

        if ($pemesananData['product_id'] !== $product->id) {
            return redirect()
                ->route('customer.pemesanan.create', $product->id)
                ->withErrors(['error' => 'Data pemesanan tidak sesuai.']);
        }

        DB::beginTransaction();
        try {
            $order = Order::create([
                'order_code' => 'ARTIV-' . strtoupper(Str::random(8)),
                'customer_id' => Auth::id(),
                'designer_id' => null,
                'product_id' => $product->id,
                'product_tier_id' => $pemesananData['product_tier_id'],
                'unit_price' => $pemesananData['unit_price'],
                'quantity' => $pemesananData['quantity'],
                'deadline' => $pemesananData['deadline'],
                'is_express' => $pemesananData['is_express'],
                'express_fee_id' => $pemesananData['express_fee_id'],
                'express_fee' => $pemesananData['express_fee'],
                'brief_note' => $pemesananData['brief_note'],
                'total_price' => $pemesananData['total_price'],
                'status' => 'pending',
            ]);

            foreach ($references as $ref) {
                OrderReference::create([
                    'order_id' => $order->id,
                    'type' => $ref['type'],
                    'file_url' => $ref['file_url'] ?? null,
                    'file_name' => $ref['file_name'] ?? null,
                    'file_size' => $ref['file_size'] ?? null,
                    'mime_type' => $ref['mime_type'] ?? null,
                    'external_url' => $ref['external_url'] ?? null,
                ]);
            }

            session()->forget(['pemesanan_data', 'pemesanan_references']);
            DB::commit();

            // Untuk sementara, tampilkan halaman placeholder
            return view('customer.pemesanan.pembayaran-placeholder', compact('product', 'pemesananData', 'order'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memproses: ' . $e->getMessage()]);
        }
    }
}