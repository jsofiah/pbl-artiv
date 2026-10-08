<?php

namespace App\Http\Middleware\Customer;

use Closure;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureOrderCanBePaid
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $order = $request->route('order');

        if (!$order instanceof Order) {
            abort(404, 'Pesanan tidak ditemukan.');
        }

        if ($order->customer_id !== Auth::id()) {
            abort(403, 'Aksi tidak sah.');
        }

        if ($order->hasUploadedPayment()) {
            return redirect()->route('customer.pemesanan.sukses', $order->id);
        }

        return $next($request);
    }
}