<?php

namespace App\Services;

use App\Models\Message;
use App\Models\Notification;
use App\Models\Order;
use Illuminate\Support\Str;

class NotificationService
{
    /** Chat baru dari designer -> notif ke customer */
    public function newChat(Message $message): ?Notification
    {
        $conversation = $message->conversation;
        $designer     = $message->sender;

        if (!$conversation || !$designer || !$designer->isDesigner()) {
            return null;
        }

        $payload = [
            'title' => "Pesan baru dari {$designer->full_name}",
            'body'  => Str::limit($message->body, 80),
            'data'  => [
            'order_id'        => $conversation->order_id,
            'conversation_id' => $conversation->id,
            'sender_name'     => $designer->full_name,
            'sender_avatar'   => $designer->avatar_url,
            'url'             => route('customer.pesanan.show', $conversation->order_id),
],
        ];

        // Notif chat belum dibaca di percakapan yang sama cukup diperbarui
        $existing = Notification::where('user_id', $conversation->customer_id)
            ->where('type', Notification::TYPE_NEW_CHAT)
            ->where('data->conversation_id', $conversation->id)
            ->unread()
            ->first();

        if ($existing) {
            $existing->forceFill($payload + ['created_at' => now()])->save();
            return $existing;
        }

        return Notification::create($payload + [
            'user_id' => $conversation->customer_id,
            'type'    => Notification::TYPE_NEW_CHAT,
        ]);
    }

    /** Pesanan selesai -> notif ke customer */
    public function orderCompleted(Order $order): Notification
    {
        return Notification::create([
            'user_id' => $order->customer_id,
            'type'    => Notification::TYPE_ORDER_COMPLETED,
            'title'   => 'Pesanan selesai',
            'body'    => "Pesanan {$order->order_code} telah selesai. Beri ulasan untuk designer-mu!",
            'data'    => [
                'order_id'   => $order->id,
                'order_code' => $order->order_code,
                'url'        => route('customer.pesanan.show', $order),
            ],
        ]);
    }
}