<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    public function create(Order $order)
    {
        $this->authorizeOrder($order);

        if ($order->review) {
            return redirect()->route('customer.pesanan.review.sukses', $order);
        }

        $order->loadMissing(['product', 'designer']);

        return view('customer.review.create', [
            'order'   => $order,
            'aspects' => Review::ASPECTS,
            'labels'  => Review::RATING_LABELS,
        ]);
    }

    public function store(Request $request, Order $order)
    {
        $this->authorizeOrder($order);

        if ($order->review) {
            return redirect()->route('customer.pesanan.review.sukses', $order);
        }

        $data = $request->validate([
            'rating'    => ['required', 'integer', 'between:1,5'],
            'aspects'   => ['nullable', 'array'],
            'aspects.*' => ['string', Rule::in(array_keys(Review::ASPECTS))],
            'comment'   => ['nullable', 'string', 'max:500'],
            'is_public' => ['nullable', 'boolean'],
        ], [
            'rating.required' => 'Pilih jumlah bintang terlebih dahulu.',
            'comment.max'     => 'Ulasan maksimal 500 karakter.',
        ]);

        Review::create([
            'order_id'    => $order->id,
            'customer_id' => $order->customer_id,
            'designer_id' => $order->designer_id,
            'rating'      => $data['rating'],
            'aspects'     => $data['aspects'] ?? [],
            'comment'     => $data['comment'] ?? null,
            'is_public'   => $request->boolean('is_public'),
        ]);

        return redirect()->route('customer.pesanan.review.sukses', $order);
    }

    public function sukses(Order $order)
    {
        $this->authorizeOrder($order);

        $review = $order->review;
        abort_if(! $review, 404);

        $order->loadMissing(['product', 'designer']);

        return view('customer.review.sukses', compact('order', 'review'));
    }

    private function authorizeOrder(Order $order): void
    {
        abort_unless($order->customer_id === auth()->id(), 403);
        abort_unless($order->status === 'completed', 403, 'Pesanan belum selesai.');
    }
}