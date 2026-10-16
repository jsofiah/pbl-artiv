<?php

namespace App\Events;

use App\Models\Message;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Message $message,
        public User $sender,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->message->conversation_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    public function broadcastWith(): array
    {
        $deliverable = $this->message->deliverable;
        $order = $this->message->conversation->order;

        $revisionData = [];
        if ($deliverable && $order) {
            $revisionData = [
                'revision_count'       => $order->revision_count,
                'revision_limit'       => \App\Models\Order::FREE_REVISION_LIMIT,
                'remaining'            => max(0, \App\Models\Order::FREE_REVISION_LIMIT - $order->revision_count),
                'can_request_revision' => $order->canRequestFreeRevision(),
            ];
        }

        return [
            'id'         => $this->message->id,
            'order_id'   => $this->message->conversation->order_id,
            'body'       => $this->message->body,
            'type'       => $this->message->type,
            'created_at' => $this->message->created_at->toISOString(),
            'sender'     => [
                'id'     => $this->sender->id,
                'name'   => $this->sender->full_name,
                'avatar' => $this->sender->avatar_url,
            ],
            'attachments'  => $this->message->attachments->map(fn ($a) => [
                'id'        => $a->id,
                'file_name' => $a->file_name,
                'file_size' => $a->file_size,
            ])->toArray(),
            'deliverable'  => $deliverable ? [
                'id'     => $deliverable->id,
                'status' => $deliverable->status,
            ] : null,

            ...$revisionData,
        ];
    }
}