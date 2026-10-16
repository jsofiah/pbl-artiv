<?php

use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);

    if (!$conversation) {
        return false;
    }

    // User harus customer ATAU designer dari conversation ini
    return $user->id === $conversation->customer_id
        || $user->id === $conversation->designer_id;
});