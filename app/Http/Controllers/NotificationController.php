<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // Halaman Notifikasi
    public function index(Request $request)
    {
        $user   = auth()->user();
        $filter = $request->query('filter', 'all');

        $query = Notification::where('user_id', $user->id)->latest();

        match ($filter) {
            'unread'  => $query->unread(),
            'refund'  => $query->whereIn('type', [Notification::TYPE_REFUND_REQUESTED, Notification::TYPE_REFUND_COMPLETED]),
            'payment' => $query->where('type', Notification::TYPE_PAYMENT),
            default   => null,
        };

        $layout = match ($user->role) {
            'designer' => 'layouts.designer',
            'admin'    => 'layouts.admin', // SESUAIKAN dengan nama file layout admin
            default    => 'layouts.customer',
        };

        return view('notifications.index', [
            'layout'      => $layout,
            'filter'      => $filter,
            'items'       => $query->paginate(15)->withQueryString(),
            'unreadCount' => Notification::where('user_id', $user->id)->unread()->count(),
        ]);
    }

    // Klik satu notifikasi: tandai dibaca lalu arahkan ke tujuan
    public function read(Notification $notification)
    {
        abort_unless($notification->user_id === auth()->id(), 403);

        $notification->markAsRead();

        return redirect($notification->data['url'] ?? auth()->user()->home());
    }

    // Tombol "Tandai dibaca"
    public function readAll()
    {
        Notification::where('user_id', auth()->id())
            ->unread()
            ->update(['read_at' => now()]);

        return back();
    }
}