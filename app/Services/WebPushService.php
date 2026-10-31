<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    public function sendToUser(User|string $user, array $payload): void
    {
        $userId = $user instanceof User ? $user->id : $user;

        $subs = PushSubscription::where('user_id', $userId)->get();
        if ($subs->isEmpty()) {
            return;
        }

        $webPush = new WebPush(['VAPID' => [
            'subject'    => config('services.webpush.subject'),
            'publicKey'  => config('services.webpush.public_key'),
            'privateKey' => config('services.webpush.private_key'),
        ]]);

        foreach ($subs as $s) {
            $webPush->queueNotification(
                Subscription::create([
                    'endpoint'        => $s->endpoint,
                    'publicKey'       => $s->public_key,
                    'authToken'       => $s->auth_token,
                    'contentEncoding' => $s->content_encoding ?: 'aes128gcm',
                ]),
                json_encode($payload)
            );
        }

        foreach ($webPush->flush() as $report) {
            if ($report->isSubscriptionExpired()) {
                // 404/410: izin dicabut user, bersihkan dari database
                PushSubscription::where('endpoint', $report->getEndpoint())->delete();
            } elseif (! $report->isSuccess()) {
                Log::warning('Web push gagal: ' . $report->getReason());
            }
        }
    }
}