<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderRefund extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'order_refunds';

    protected $fillable = [
        'order_id',
        'customer_id',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'amount',
        'status',
        'processed_by',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }

    // ==================== Konstanta ====================

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';

    // ==================== Relasi ====================

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    // ==================== Helper ====================

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function markAsCompleted(User $admin): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'processed_by' => $admin->id,
            'processed_at' => now(),
        ]);
    }
}