<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class BerandaController extends Controller
{
    // Dipakai bersama oleh halaman Katalog dan Pesanan Saya
    private const CATEGORIES = [
        'Poster',
        'Logo',
        'Banner',
        'Brosur / Leaflet',
        'Postingan Sosial Media',
        'Sticker',
        'Scrapbook',
        'Presentasi (PowerPoint)',
        'Twibbon',
        'Infografis',
        'UI Design',
        'Pin Button',
        'Keychain',
    ];

    public function index()
    {
        $popularProducts = Product::where('is_active', true)
            ->with(['tiers' => fn($q) => $q->where('is_active', true)])
            ->withCount([
                'orders' => function ($query) {
                    $query->whereNotIn('status', [
                        Order::STATUS_CANCELLED,
                        Order::STATUS_REFUNDED,
                    ]);
                }
            ])
            ->orderByDesc('orders_count')
            ->limit(4)
            ->get();

        if ($popularProducts->sum('orders_count') === 0) {
            $popularProducts = Product::where('is_active', true)
                ->with(['tiers' => fn($q) => $q->where('is_active', true)])
                ->orderByDesc('created_at')
                ->limit(4)
                ->get();
        }

        return view('customer.beranda', compact('popularProducts'));
    }

    public function katalog(Request $request)
    {
        $query = Product::where('is_active', true)
            ->with(['tiers' => function ($q) {
                $q->where('is_active', true)->orderBy('price');
            }]);

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        if ($request->filled('category') && $request->category !== 'Semua Kategori') {
            $query->where('name', 'ilike', "%{$request->category}%");
        }

        if ($request->filled('price')) {
            match ($request->price) {
                'under_500'   => $query->where('price', '<', 500000),
                '500_1000'    => $query->whereBetween('price', [500000, 1000000]),
                'above_1000'  => $query->where('price', '>', 1000000),
                default       => null,
            };
        }

        match ($request->sort) {
            'cheapest'  => $query->orderBy('price', 'asc'),
            'expensive' => $query->orderBy('price', 'desc'),
            default     => $query->orderBy('name', 'asc'),
        };

        $products = $query->paginate(12)->withQueryString();

        $categories = self::CATEGORIES;

        return view('customer.katalog', compact('products', 'categories'));
    }

    public function detailKatalog(Product $product)
    {
        abort_if(!$product->is_active, 404);

        $product->load(['tiers' => function ($query) {
            $query->where('is_active', true)->orderBy('price');
        }]);

        return view('customer.katalog-detail', compact('product'));
    }

    // ==================== Pesanan Saya ====================

    public function pesanan(Request $request)
    {
        $tab = $request->query('tab') === 'riwayat' ? 'riwayat' : 'aktif';

        // Hanya pesanan milik customer yang sedang login
        $mine = Order::where('customer_id', $request->user()->id);

        $orders = (clone $mine)
            ->with(['product', 'productTier', 'designer'])      // cegah N+1 query
            ->when($tab === 'aktif', fn ($q) => $q->aktif(), fn ($q) => $q->riwayat())
            ->filter($request->only(['q', 'status', 'kategori']))
            ->urutkan($request->query('urut', 'deadline'))
            ->paginate(10)
            ->withQueryString();                                // filter tetap aktif saat pindah halaman

        return view('customer.pesanan', [
            'orders'        => $orders,
            'tab'           => $tab,
            'countAktif'    => (clone $mine)->aktif()->count(),
            'countRiwayat'  => (clone $mine)->riwayat()->count(),
            'statusOptions' => collect($tab === 'aktif' ? Order::AKTIF : Order::RIWAYAT)
                ->mapWithKeys(fn ($s) => [$s => Order::STATUS_LABELS[$s]]),
            'categories'    => self::CATEGORIES,
        ]);
    }
}