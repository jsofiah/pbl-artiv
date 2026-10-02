<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'order_logs';

    protected $fillable = [
        'order_id',
        'actor_id',
        'action',
        'status',
        'note',
    ];

    // ==================== Konstanta Action ====================

    public const ACTION_CREATE = 'create';
    public const ACTION_ASSIGN = 'assign';
    public const ACTION_REASSIGN = 'reassign';
    public const ACTION_DELIVERABLE_SENT = 'deliverable_sent';
    public const ACTION_REVISION_REQUESTED = 'revision_requested';
    public const ACTION_COMPLETED = 'completed';
    public const ACTION_CANCELLED = 'cancelled';
    public const ACTION_DEADLINE_MISSED = 'deadline_missed';
    public const ACTION_CANNOT_CONTINUE = 'cannot_continue';
    public const ACTION_CUSTOMER_CHOOSE_REFUND = 'customer_choose_refund';
    public const ACTION_CUSTOMER_CHOOSE_CONTINUE = 'customer_choose_continue';
    public const ACTION_REFUND_COMPLETED = 'refund_completed';

    // ==================== Relasi ====================

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}