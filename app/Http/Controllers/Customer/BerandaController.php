<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class BerandaController extends Controller
{
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
            ->limit(8)
            ->get();

        if ($popularProducts->sum('orders_count') === 0) {
            $popularProducts = Product::where('is_active', true)
                ->with(['tiers' => fn($q) => $q->where('is_active', true)])
                ->orderByDesc('created_at')
                ->limit(8)
                ->get();
        }

        $heroProducts = Product::where('is_active', true)
            ->with(['tiers' => fn($q) => $q->where('is_active', true)])
            ->latest()
            ->take(2)
            ->get();

        // Mapping grup → keyword yang dicocokkan ke name
        $categoryGroups = [
            'Promosi & Informasi' => [
                'slug' => 'promosi',
                'items' => ['Poster', 'Banner', 'Brosur', 'Postingan', 'Infografis', 'Twibbon'],
            ],
            'Branding & Identitas' => [
                'slug' => 'branding',
                'items' => ['Logo', 'Sticker', 'UI', 'Pin', 'Keychain'],
            ],
            'Presentasi & Personal' => [
                'slug' => 'presentasi',
                'items' => ['Presentasi', 'Scrapbook', 'PowerPoint'],
            ],
        ];

        // Ambil 3 thumbnail per grup — pakai name ilike
        $categoryThumbs = [];
        foreach ($categoryGroups as $label => $group) {
            $thumbs = Product::where('is_active', true)
                ->where(function ($q) use ($group) {
                    foreach ($group['items'] as $keyword) {
                        $q->orWhere('name', 'ilike', "%{$keyword}%");
                    }
                })
                ->whereNotNull('thumbnail_url')
                ->latest()
                ->take(3)
                ->pluck('thumbnail_url')
                ->map(fn($url) => \App\Helpers\R2Helper::url($url))
                ->values()
                ->toArray();

            $categoryThumbs[$label] = $thumbs;
        }

        return view('customer.beranda', compact('popularProducts', 'heroProducts', 'categoryGroups', 'categoryThumbs'));
    }

    public function katalog(Request $request)
    {
        $query = Product::where('is_active', true)
            ->with(['tiers' => function ($q) {
                $q->where('is_active', true)->orderBy('price');
            }]);

        // Search
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                ->orWhere('description', 'ilike', "%{$search}%");
            });
        }

        /// Filter kategori tunggal (dropdown) — pakai name
        if ($request->filled('category') && $request->category !== 'Semua Kategori') {
            $query->where('name', 'ilike', "%{$request->category}%");
        }

        // Filter multi-kategori (dari beranda) — pakai name
        if ($request->filled('categories')) {
            $categories = array_filter(explode(',', $request->categories));
            if (!empty($categories)) {
                $query->where(function ($q) use ($categories) {
                    foreach ($categories as $keyword) {
                        $q->orWhere('name', 'ilike', "%{$keyword}%");
                    }
                });
            }
        }

        $allProducts = $query->get();

        match ($request->sort) {
            'cheapest' => $allProducts = $allProducts->sortBy(function ($product) {
                return $product->tiers->min('price') ?? PHP_INT_MAX;
            })->values(),

            'expensive' => $allProducts = $allProducts->sortByDesc(function ($product) {
                return $product->tiers->min('price') ?? 0;
            })->values(),

            'popular' => $allProducts = $allProducts->sortByDesc(function ($product) {
                return $product->orders()
                    ->whereNotIn('status', [
                        \App\Models\Order::STATUS_CANCELLED,
                        \App\Models\Order::STATUS_REFUNDED,
                    ])
                    ->count();
            })->values(),

            default => $allProducts = $allProducts->sortBy('name')->values(),
        };

        $page = $request->get('page', 1);
        $perPage = 12;
        $products = new \Illuminate\Pagination\LengthAwarePaginator(
            $allProducts->forPage($page, $perPage)->values(),
            $allProducts->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $categories = Product::where('is_active', true)
        ->select('name')
        ->distinct()
        ->pluck('name')
        ->take(20)
        ->toArray();

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

    public function pesanan()
    {
        return view('customer.pesanan');
    }
}