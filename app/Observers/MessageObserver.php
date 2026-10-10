<?php

namespace App\Observers;

use App\Models\Message;
use App\Services\NotificationService;

class MessageObserver
{
    public function created(Message $message): void
    {
        app(NotificationService::class)->newChat($message);
    }
}