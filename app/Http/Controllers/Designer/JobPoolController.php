<?php

namespace App\Http\Controllers\Designer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderLog;
use App\Models\OrderReference;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class JobPoolController extends Controller
{
    public function index(Request $request): View
    {
        $q        = trim((string) $request->query('q', ''));
        $kategori = $request->query('kategori');
        $urutkan  = $request->query('urutkan', 'terbaru');

        $query = Order::query()
            ->whereNull('designer_id')
            ->whereIn('status', ['pending', 'waiting_designer'])
            ->with(['product', 'productTier', 'customer', 'references']);

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('order_code', 'ilike', "%{$q}%")
                    ->orWhereHas('product', fn($p) => $p->where('name', 'ilike', "%{$q}%"));
            });
        }

        if ($kategori && $kategori !== 'semua') {
            $query->whereHas('product', fn($p) => $p->where('name', 'ilike', "%{$kategori}%"));
        }

        match ($urutkan) {
            'deadline_asc'  => $query->orderBy('deadline', 'asc'),
            'deadline_desc' => $query->orderBy('deadline', 'desc'),
            'harga_asc'     => $query->orderBy('total_price', 'asc'),
            'harga_desc'    => $query->orderBy('total_price', 'desc'),
            'terlama'       => $query->orderBy('created_at', 'asc'),
            default         => $query->orderByDesc('created_at'),
        };

        $totalTersedia = (clone $query)
            ->where(function ($q) {
                $q->whereNull('deadline')
                    ->orWhere('deadline', '>=', now()->startOfDay());
            })
            ->count();

        $jobPool = $query->paginate(15)->withQueryString();

        $kategoris = Product::where('is_active', true)
            ->distinct()
            ->orderBy('name')
            ->pluck('name');

        return view('designer.job-pool', [
            'jobPool'       => $jobPool,
            'totalTersedia' => $totalTersedia,
            'kategoris'     => $kategoris,
            'q'             => $q,
            'kategori'      => $kategori,
            'urutkan'       => $urutkan,
        ]);
    }

    public function show(string $order): View
    {
        $order = Order::with([
            'customer',
            'product',
            'productTier',
            'expressFee',
            'references',
        ])
            ->whereNull('designer_id')
            ->whereIn('status', ['pending', 'waiting_designer'])
            ->findOrFail($order);

        return view('designer.detail-job', compact('order'));
    }

    public function ambil(Request $request, string $order)
    {
        $orderModel = Order::whereNull('designer_id')
            ->whereIn('status', ['pending', 'waiting_designer'])
            ->findOrFail($order);

        if ($orderModel->deadline && $orderModel->deadline->isPast()) {
            return back()->withErrors([
                'error' => 'Order ini sudah lewat deadline dan tidak bisa diambil.',
            ]);
        }

        DB::beginTransaction();
        try {
            $orderModel = Order::lockForUpdate()->findOrFail($order);

            if ($orderModel->designer_id !== null) {
                DB::rollBack();
                return back()->withErrors(['error' => 'Job sudah diambil designer lain.']);
            }

            $orderModel->update([
                'designer_id' => Auth::id(),
                'assigned_at' => now(),
                'status'      => 'in_progress',
            ]);

            OrderLog::create([
                'order_id' => $orderModel->id,
                'actor_id' => Auth::id(),
                'action'   => 'order_taken',
                'status'   => 'in_progress',
                'note'     => 'Designer mengambil order dari job pool.',
            ]);

            DB::commit();

            return redirect()
                ->route('designer.job-pool.sukses', $orderModel->id)
                ->with('status', 'Order berhasil diambil.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal mengambil order: ' . $e->getMessage()]);
        }
    }

    public function sukses(string $order): View
    {
        $order = Order::with(['customer', 'product', 'productTier', 'expressFee'])
            ->where('designer_id', Auth::id())
            ->findOrFail($order);

        return view('designer.sukses', compact('order'));
    }

    public function downloadReference(string $order, string $reference)
    {
        $orderModel = Order::whereNull('designer_id')
            ->whereIn('status', ['pending', 'waiting_designer'])
            ->findOrFail($order);

        $ref = OrderReference::where('order_id', $orderModel->id)
            ->where('id', $reference)
            ->firstOrFail();

        if ($ref->type !== 'file' || !$ref->file_url) {
            abort(404, 'File tidak ditemukan.');
        }

        return $this->streamFileFromR2($ref->file_url, $ref->file_name ?? 'file');
    }

    public function previewReference(string $order, string $reference)
    {
        $orderModel = Order::whereNull('designer_id')
            ->whereIn('status', ['pending', 'waiting_designer'])
            ->findOrFail($order);

        $ref = OrderReference::where('order_id', $orderModel->id)
            ->where('id', $reference)
            ->firstOrFail();

        if ($ref->type !== 'file' || !$ref->file_url) {
            return response()->json(['error' => 'File tidak ditemukan.'], 404);
        }

        $imageMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/jpg'];
        if (!in_array(strtolower($ref->mime_type ?? ''), $imageMimes)) {
            return response()->json(['error' => 'Preview hanya untuk gambar.'], 404);
        }

        $path = ltrim($ref->file_url, '/');
        if (preg_match('#^https?://[^/]+/(.+)$#', $path, $m)) {
            $path = $m[1];
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $r2Disk */
        $r2Disk = Storage::disk('r2');
        $r2Url  = $r2Disk->temporaryUrl($path, now()->addMinutes(15));

        $response = Http::get($r2Url);

        if ($response->failed()) {
            return response()->json(['error' => 'Gagal fetch file.'], 404);
        }

        $base64 = base64_encode($response->body());
        $mime   = $ref->mime_type ?? 'image/jpeg';

        return response()->json([
            'url'  => "data:{$mime};base64,{$base64}",
            'name' => $ref->file_name,
        ]);
    }

    private function streamFileFromR2(string $path, string $filename)
    {
        $response = Http::withOptions([
            'stream' => true,
        ])->get(\App\Helpers\R2Helper::url($path));

        if ($response->failed()) {
            abort(404, 'Gagal mengambil file.');
        }

        return response()->streamDownload(function () use ($response) {
            echo $response->body();
        }, $filename);
    }
}
