<?php

namespace App\Events;

use App\Models\Deliverable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliverableUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Deliverable $deliverable,
        public string $conversationId,
        public string $event,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.' . $this->conversationId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'deliverable.updated';
    }

    public function broadcastWith(): array
    {
        $order = $this->deliverable->order;

        return [
            'deliverable_id' => $this->deliverable->id,
            'status'         => $this->deliverable->status,
            'revision_note'  => $this->deliverable->revision_note,
            'approved_at'    => $this->deliverable->approved_at?->toISOString(),
            'event'          => $this->event,

            // Info revisi
            'order_status'         => $order->status,
            'revision_count'  => $order->revision_count,
            'revision_limit'  => \App\Models\Order::FREE_REVISION_LIMIT,
            'remaining'       => max(0, \App\Models\Order::FREE_REVISION_LIMIT - $order->revision_count),
            'can_request_revision' => $order->canRequestFreeRevision(),
        ];
    }
}