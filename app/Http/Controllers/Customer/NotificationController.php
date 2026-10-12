<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Notification;

class NotificationController extends Controller
{
    // Klik satu notif: tandai dibaca lalu arahkan ke halaman tujuan
    public function read(Notification $notification)
    {
        abort_unless($notification->user_id === auth()->id(), 403);

        $notification->markAsRead();

        return redirect($notification->data['url'] ?? url()->previous());
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