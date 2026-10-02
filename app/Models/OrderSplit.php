<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderSplit extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'order_splits';

    protected $fillable = [
        'order_id',
        'designer_id',
        'split_type',
        'commission_rate',
        'gross_amount',
        'commission_amount',
        'designer_earning',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:2',
            'gross_amount' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'designer_earning' => 'decimal:2',
        ];
    }

    // ==================== Konstanta ====================

    public const TYPE_NORMAL = 'normal';           // 90/10
    public const TYPE_REASSIGNMENT = 'reassignment'; // 95/5

    public const COMMISSION_NORMAL = 10.00;
    public const COMMISSION_REASSIGNMENT = 5.00;

    // ==================== Relasi ====================

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function designer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'designer_id');
    }

    // ==================== Helper ====================

    public function isReassignment(): bool
    {
        return $this->split_type === self::TYPE_REASSIGNMENT;
    }

    public function isNormal(): bool
    {
        return $this->split_type === self::TYPE_NORMAL;
    }

    public function designerPercentage(): float
    {
        return 100 - (float) $this->commission_rate;
    }
}