<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\GalleryItem;
use Illuminate\Support\Facades\Storage;

class GalleryController extends Controller
{
    private const ACCENTS = ['#D5FC55', '#C4B5FD', '#F9A8D4', '#A5F3FC', '#FDE68A'];

    private function resolveUrl(?string $path, int $minutes = 60): ?string
    {
        if (! $path) return null;

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $path = ltrim($path, '/');
        if (preg_match('#^https?://[^/]+/(.+)$#', $path, $m)) {
            $path = $m[1];
        }

        try {
            return Storage::disk('r2')->temporaryUrl($path, now()->addMinutes($minutes));
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function index()
    {
        $allItems = GalleryItem::active()
            ->with(['product', 'order.designer'])
            ->latest()
            ->get();

        $featured = $allItems
            ->groupBy(fn ($item) => $item->product_id ?? 'manual-' . $item->id)
            ->take(5)
            ->values()
            ->map(function ($group, $i) {
                $first = $group->first();

                return [
                    'title'       => $first->title ?: ($first->product->name ?? 'Karya Desain'),
                    'category'    => $first->product->name ?? 'Desain',
                    'description' => $first->description
                        ?: 'Hasil desain pilihan dari kreator ARTIV untuk kebutuhan '
                            . strtolower($first->product->name ?? 'branding') . '.',
                    'image'       => $this->resolveUrl($first->image_url),
                    'designer'    => $first->order?->designer?->full_name ?? 'Desainer ARTIV',
                    'tags'        => array_filter([
                        $first->product->name ?? null,
                        $first->order?->order_code ? 'Order #' . $first->order->order_code : null,
                    ]),
                    'href'        => route('customer.katalog'),
                    'accent'      => self::ACCENTS[$i % count(self::ACCENTS)],
                ];
            })
            ->all();

        $works = $allItems
            ->groupBy(fn ($item) => $item->product_id ?? 'manual-' . $item->id)
            ->values()
            ->map(function ($group) {
                $first = $group->first();

                $images = $group
                    ->map(fn ($it) => $this->resolveUrl($it->image_url))
                    ->filter()
                    ->values()
                    ->all();

                return [
                    'title'    => $first->title ?: ($first->product->name ?? 'Karya Desain'),
                    'category' => $first->product->name ?? 'Desain',
                    'designer' => $first->order?->designer?->full_name ?? 'Desainer ARTIV',
                    'images'   => $images,
                    'image'    => $images[0] ?? null,
                ];
            })
            ->all();

        return view('customer.galeri', compact('featured', 'works'));
    }
}