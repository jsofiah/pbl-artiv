<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'payments';

    protected $fillable = [
        'order_id',
        'type',
        'method',
        'amount',
        'proof_url',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    // ==================== Konstanta Type ====================

    public const TYPE_ORDER = 'order';
    public const TYPE_REVISION_FEE = 'revision_fee';

    // ==================== Konstanta Status ====================

    public const STATUS_PENDING = 'pending';
    public const STATUS_BERHASIL = 'berhasil';
    public const STATUS_GAGAL = 'gagal';

    // ==================== Relasi ====================

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // ==================== Scope ====================

    public function scopeSuccess($query)
    {
        return $query->where('status', self::STATUS_BERHASIL);
    }

    // ==================== Helper ====================

    public function isOrderPayment(): bool
    {
        return $this->type === self::TYPE_ORDER;
    }

    public function isRevisionFee(): bool
    {
        return $this->type === self::TYPE_REVISION_FEE;
    }

    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_BERHASIL;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function markAsSuccess(): void
    {
        $this->update([
            'status' => self::STATUS_BERHASIL,
            'paid_at' => now(),
        ]);
    }
}