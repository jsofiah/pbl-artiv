<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'data',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'read_at' => 'datetime',
        ];
    }

    // ==================== Konstanta Type ====================

    public const TYPE_NEW_ORDER = 'new_order';
    public const TYPE_ORDER_TAKEN = 'order_taken';
    public const TYPE_REVISION_REQUESTED = 'revision_requested';
    public const TYPE_REASSIGNMENT = 'reassignment';
    public const TYPE_CANNOT_CONTINUE = 'cannot_continue';
    public const TYPE_CUSTOMER_DECISION = 'customer_decision';
    public const TYPE_REFUND_REQUESTED = 'refund_requested';
    public const TYPE_REFUND_COMPLETED = 'refund_completed';
    public const TYPE_DELIVERABLE_SENT = 'deliverable_sent';
    public const TYPE_ORDER_COMPLETED = 'order_completed';
    public const TYPE_SUSPENSION = 'suspension';

    // ==================== Relasi ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ==================== Scope ====================

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    // ==================== Helper ====================

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markAsRead(): void
    {
        if (!$this->isRead()) {
            $this->update(['read_at' => now()]);
        }
    }
}