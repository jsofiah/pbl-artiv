<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'orders';

    protected $fillable = [
        'order_code',
        'customer_id',
        'designer_id',
        'product_id',
        'product_tier_id',
        'unit_price',
        'quantity',
        'deadline',
        'assigned_at',
        'is_express',
        'express_fee_id',
        'express_fee',
        'brief_note',
        'total_price',
        'status',
        'revision_count',
        'is_late',
        'late_at',
        'cannot_continue_reason',
        'cannot_continue_at',
        'customer_decision',
        'customer_decision_at',
        'is_urgent',
        'original_deadline',
        'reassign_count',
        'approved_at',
        'completed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'express_fee' => 'decimal:2',
            'total_price' => 'decimal:2',
            'is_express' => 'boolean',
            'is_late' => 'boolean',
            'is_urgent' => 'boolean',
            'deadline' => 'datetime',
            'assigned_at' => 'datetime',
            'late_at' => 'datetime',
            'cannot_continue_at' => 'datetime',
            'customer_decision_at' => 'datetime',
            'original_deadline' => 'datetime',
            'approved_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    // ==================== Konstanta Status ====================

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_WAITING_CUSTOMER_DECISION = 'waiting_customer_decision';
    public const STATUS_REASSIGNMENT_NEEDED = 'reassignment_needed';
    public const STATUS_REFUND_REQUESTED = 'refund_requested';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    // ==================== Konstanta Decision ====================

    public const DECISION_REFUND = 'refund';
    public const DECISION_CONTINUE = 'continue';

    // ==================== Konstanta Aturan ====================

    public const FREE_REVISION_LIMIT = 3;
    public const REASSIGN_DEADLINE_DAYS = 3;

    // ==================== Relasi ====================

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function designer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'designer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productTier(): BelongsTo
    {
        return $this->belongsTo(ProductTier::class);
    }

    public function expressFee(): BelongsTo
    {
        return $this->belongsTo(ExpressFee::class);
    }

    public function references(): HasMany
    {
        return $this->hasMany(OrderReference::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function refund(): HasOne
    {
        return $this->hasOne(OrderRefund::class);
    }

    public function conversation(): HasOne
    {
        return $this->hasOne(Conversation::class);
    }

    public function deliverables(): HasMany
    {
        return $this->hasMany(Deliverable::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(OrderLog::class);
    }

    public function split(): HasOne
    {
        return $this->hasOne(OrderSplit::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    // ==================== Scope ====================

    public function scopeUrgent($query)
    {
        return $query->where('is_urgent', true);
    }

    public function scopeInJobPool($query)
    {
        return $query->whereNull('designer_id')
            ->whereIn('status', [
                self::STATUS_PENDING,
                self::STATUS_REASSIGNMENT_NEEDED,
            ]);
    }

    // ==================== Helper Status ====================

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isWaitingCustomerDecision(): bool
    {
        return $this->status === self::STATUS_WAITING_CUSTOMER_DECISION;
    }

    public function isReassignmentNeeded(): bool
    {
        return $this->status === self::STATUS_REASSIGNMENT_NEEDED;
    }

    public function isRefundRequested(): bool
    {
        return $this->status === self::STATUS_REFUND_REQUESTED;
    }

    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isUrgentReassignment(): bool
    {
        return $this->is_urgent
            && $this->status === self::STATUS_REASSIGNMENT_NEEDED;
    }

    // ==================== Helper Revisi ====================

    public function canRequestFreeRevision(): bool
    {
        return $this->revision_count < self::FREE_REVISION_LIMIT;
    }

    public function needsPaidRevision(): bool
    {
        return $this->revision_count >= self::FREE_REVISION_LIMIT;
    }

    // ==================== Helper Deadline ====================

    public function isDeadlineMissed(): bool
    {
        return $this->deadline
            && $this->deadline->isPast()
            && !in_array($this->status, [
                self::STATUS_COMPLETED,
                self::STATUS_CANCELLED,
                self::STATUS_REFUNDED,
            ]);
    }

    // ==================== Helper Reassignment ====================

    public function hasBeenReassigned(): bool
    {
        return $this->reassign_count > 0;
    }
}