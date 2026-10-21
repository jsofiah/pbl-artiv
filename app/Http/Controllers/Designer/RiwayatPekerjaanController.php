<?php

namespace App\Http\Controllers\Designer;

use App\Http\Controllers\Controller;
use App\Models\MessageAttachment;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RiwayatPekerjaanController extends Controller
{
    /**
     * Daftar riwayat pekerjaan designer (order berstatus completed).
     */
    public function index(Request $request)
    {
        $orders = $this->query($request)->paginate(10)->withQueryString();

        // Dropdown kategori: hanya layanan yang pernah selesai dikerjakan designer ini.
        // Otomatis bertambah saat ada pekerjaan selesai di kategori baru.
        $kategori = Product::whereIn('id',
                Order::where('designer_id', auth()->id())
                    ->where('status', Order::STATUS_COMPLETED)
                    ->select('product_id')
            )
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('designer.riwayat', [
            'orders'   => $orders,
            'kategori' => $kategori,
            'filters'  => $request->only(['q', 'kategori', 'urut']),
        ]);
    }

    /**
     * Detail review dari customer untuk satu pekerjaan.
     */
    public function show(Order $order)
    {
        abort_unless($order->designer_id === auth()->id() && $order->status === Order::STATUS_COMPLETED, 404);

        $order->load([
            'product:id,name',
            'productTier',
            'customer:id,full_name,avatar_url',
            'review',
            'split',
            'references',
            'deliverables.message.attachments',
            'logs',
        ]);

        // Rating rata-rata keseluruhan milik designer ini
        $rataRata = round((float) Review::where('designer_id', auth()->id())->avg('rating'), 1);

        return view('designer.riwayat.show', compact('order', 'rataRata'));
    }

    /**
     * Unduh file hasil desain (disimpan di bucket R2 privat).
     */
    public function downloadHasil(Order $order, string $attachment)
    {
        abort_unless(
            $order->designer_id === auth()->id() && $order->status === Order::STATUS_COMPLETED,
            404
        );

        // Hanya lampiran dari pesan yang terhubung ke deliverable order ini
        // (sama dengan file yang ditampilkan di halaman Lihat Review)
        $att = MessageAttachment::where('id', $attachment)
            ->whereHas('message.deliverable', fn ($d) => $d->where('order_id', $order->id))
            ->firstOrFail();

        $key = ltrim($att->file_url, '/');
        if (preg_match('#^https?://[^/]+/(.+)$#', $key, $m)) {
            $key = $m[1];
        }

        $disk = Storage::disk('r2'); // bucket privat
        try {
            $ada = $disk->exists($key);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Penyimpanan file tidak dapat dihubungi. Coba lagi nanti.');
        }

        if (! $ada) {
            return back()->with('error', 'File hasil tidak ditemukan di penyimpanan.');
        }

        return $disk->download($key, $att->file_name ?? 'hasil-desain');
    }

    /**
     * Ekspor CSV sesuai filter yang sedang aktif.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        $orders = $this->query($request)->get();

        return response()->streamDownload(function () use ($orders) {
            $out = fopen('php://output', 'w');

            // BOM supaya Excel membaca UTF-8 dengan benar
            fwrite($out, "\xEF\xBB\xBF");

            // Pemisah titik koma: Excel dengan pengaturan regional Indonesia memakai ';'
            fputcsv($out, ['Kode Order', 'Layanan', 'Customer', 'Tanggal Selesai', 'Kompensasi', 'Rating'], ';');

            foreach ($orders as $o) {
                fputcsv($out, [
                    $o->order_code,
                    $o->product->name ?? '-',
                    $o->customer->full_name ?? '-',
                    optional($o->completed_at ?? $o->updated_at)->format('d/m/Y'),
                    (int) round($o->split->designer_earning ?? 0),
                    $o->review->rating ?? '-',
                ], ';');
            }
            fclose($out);
        }, 'riwayat-pekerjaan-' . now()->format('Ymd') . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Halaman cetak laporan (tanpa sidebar, langsung window.print()).
     */
    public function cetak(Request $request)
    {
        return view('designer.riwayat.cetak', [
            'orders' => $this->query($request)->get(),
        ]);
    }

    private function query(Request $request)
    {
        $q = Order::query()
            ->where('designer_id', auth()->id())
            ->where('status', 'completed')
            ->with(['product:id,name', 'productTier', 'customer:id,full_name', 'review:id,order_id,rating', 'split']);

        if ($search = trim((string) $request->query('q'))) {
            $q->where(function ($w) use ($search) {
                $w->where('order_code', 'ilike', "%{$search}%")          // PostgreSQL: ilike
                  ->orWhere('brief_note', 'ilike', "%{$search}%")
                  ->orWhereHas('customer', fn ($c) => $c->where('full_name', 'ilike', "%{$search}%"))
                  ->orWhereHas('product', fn ($p) => $p->where('name', 'ilike', "%{$search}%"));
            });
        }

        if ($kategori = $request->query('kategori')) {
            $q->where('product_id', $kategori);
        }

        return match ($request->query('urut')) {
            'terlama' => $q->orderBy('completed_at'),
            'rating'  => $q->orderByDesc(
                \App\Models\Review::select('rating')->whereColumn('reviews.order_id', 'orders.id')->limit(1)
            ),
            default   => $q->orderByDesc('completed_at'),
        };
    }
}