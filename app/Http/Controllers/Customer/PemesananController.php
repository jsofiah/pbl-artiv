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

        $tiers = $product->tiers->map(function ($tier) {
            return [
                'id'    => $tier->id,
                'name'  => $tier->name,
                'price' => (float) $tier->price,
            ];
        })->values();


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
        })->values();

        $pemesananData = session('pemesanan_data', []);
        $references = session('pemesanan_references', []);

        if (!empty($pemesananData) && ($pemesananData['product_id'] ?? null) !== $product->id) {
            session()->forget(['pemesanan_data', 'pemesanan_references']);
            $pemesananData = [];
            $references = [];
        }

        return view('customer.pemesanan.create', compact(
            'product',
            'tiers',
            'expressFees',
            'defaultTier',
            'expressFeeMap',
            'pemesananData',
            'references'
        ));
    }

    public function ringkasan(Request $request, Product $product)
    {
        $validated = $request->validate([
            'product_tier_id' => 'required|exists:product_tiers,id',
            'quantity' => 'required|integer|min:1',
            'deadline_option' => 'required|in:default,express',
            'target_deadline' => 'required_if:deadline_option,express|nullable|date|after:today',
            'brief_note' => 'required|string|min:10|max:1000',

            'reference_files.*' => 'nullable|file|max:20480|mimes:jpg,jpeg,png,pdf,zip',
            'reference_links.*' => 'nullable|url',                         // ← nullable aja
            'existing_files.*' => 'nullable|string',
        ], [
            'target_deadline.required_if' => 'Tanggal deadline wajib diisi untuk Express.',
            'target_deadline.after' => 'Tanggal minimal H+2 dari hari ini.',
            'brief_note.required' => 'Catatan brief desain wajib diisi.',
            'brief_note.min' => 'Catatan brief minimal 10 karakter.',
            'reference_links.*.url' => 'Format tautan tidak valid (harus URL).',
        ]);

        $uploadedFiles = $request->file('reference_files') ?? [];
        $hasNewFile = collect($uploadedFiles)->filter()->isNotEmpty();
        $hasOldFile = $request->filled('existing_files');
        $hasLink = collect($request->reference_links ?? [])
            ->filter(fn($l) => !empty(trim($l)))
            ->isNotEmpty();

        if (!$hasNewFile && !$hasOldFile && !$hasLink) {
            return back()
                ->withErrors(['reference_links' => 'Minimal salah satu: unggah file ATAU isi tautan referensi.'])
                ->withInput();
        }

        $tier = ProductTier::where('product_id', $product->id)
            ->findOrFail($validated['product_tier_id']);

        $deadline = now()->addDays(7);
        $expressFee = null;

        if ($validated['deadline_option'] === 'express') {
            if (empty($validated['target_deadline'])) {
                return back()->withErrors(['target_deadline' => 'Tanggal deadline wajib diisi.'])->withInput();
            }

            $deadline = \Carbon\Carbon::parse($validated['target_deadline'])->startOfDay();
            $daysDiff = (int) now()->startOfDay()->diffInDays($deadline, false);

            if ($daysDiff < 2) {
                return back()->withErrors(['target_deadline' => 'Minimal H+2 dari sekarang.'])->withInput();
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
        $oldReferences = session('pemesanan_references', []);

        if ($request->filled('existing_files')) {
            foreach ($request->existing_files as $url) {
                $found = collect($oldReferences)->firstWhere('file_url', $url);
                if ($found) {
                    $references[] = $found;
                }
            }
        }

        if ($request->hasFile('reference_files')) {
            $storage = app(R2StorageService::class);
            foreach ($request->file('reference_files') as $file) {
                try {
                    $path = $storage->uploadPrivate($file, 'temp/references');
                    $references[] = [
                        'type' => 'file',
                        'file_url' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_size' => $file->getSize(),
                        'mime_type' => $file->getMimeType(),
                    ];
                } catch (\Exception $e) {
                    \Log::error('R2 upload failed: ' . $e->getMessage());
                }
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

        return redirect()->route('customer.pemesanan.ringkasan.show', $product->id);
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

        $storage = app(R2StorageService::class);
        $movedReferences = [];

        foreach ($references as $ref) {
            if ($ref['type'] === 'file' && !empty($ref['file_url'])) {
                $newPath = str_replace('temp/references/', 'references/', $ref['file_url']);
                
                if ($storage->movePrivate($ref['file_url'], $newPath)) {
                    $ref['file_url'] = $newPath;  // update path ke yang baru
                }
            }
            $movedReferences[] = $ref;
        }

        $references = $movedReferences;

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

            return view('customer.pemesanan.pembayaran-placeholder', compact('product', 'pemesananData', 'order'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal memproses: ' . $e->getMessage()]);
        }
    }

    public function hapusReferensi(Request $request, Product $product)
    {
        $request->validate([
            'index' => 'required|integer|min:0',
        ]);

        $references = session('pemesanan_references', []);
        $index = $request->input('index');

        if (isset($references[$index])) {
            $ref = $references[$index];

            if ($ref['type'] === 'file' && !empty($ref['file_url'])) {
                try {
                    $storage = app(R2StorageService::class);
                    $storage->deletePrivate($ref['file_url']);
                } catch (\Exception $e) {
                    \Log::error('Gagal hapus file R2: ' . $e->getMessage());
                }
            }

            unset($references[$index]);
            $references = array_values($references);
            session(['pemesanan_references' => $references]);
        }

        return response()->json(['success' => true]);
    }
}