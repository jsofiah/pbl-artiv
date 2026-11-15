<?php

namespace App\Jobs;

use App\Models\Notification;
use App\Services\WebPushService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWebPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;
    public bool $deleteWhenMissingModels = true;

    public function __construct(public Notification $notification) {}

    public function handle(WebPushService $push): void
    {
        $n = $this->notification;

        $push->sendToUser($n->user_id, [
            'title' => $n->title,
            'body'  => $n->body,
            'icon'  => asset('images/logo-artiv.png'),   // sesuaikan path logo
            'tag'   => $n->data['conversation_id'] ?? $n->id,
            'data'  => ['url' => route('notifikasi.read', $n)],
        ]);
    }
}