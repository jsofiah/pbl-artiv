<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DesignerStat extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'designer_stats';

    protected $fillable = [
        'designer_id',
        'total_orders',
        'completed_orders',
        'average_rating',
        'total_earning',
        'daily_order_limit',
        'max_active_orders',
        'availability_status',
        'late_count',
        'total_late_count',
        'suspend_count',
        'last_suspended_at',
        'suspended_until',
    ];

    protected function casts(): array
    {
        return [
            'average_rating' => 'decimal:2',
            'total_earning' => 'decimal:2',
            'last_suspended_at' => 'datetime',
            'suspended_until' => 'datetime',
        ];
    }

    // ==================== Konstanta ====================

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_UNAVAILABLE = 'unavailable';
    public const STATUS_SUSPENDED = 'suspended';

    public const DEFAULT_DAILY_LIMIT = 2;
    public const DEFAULT_MAX_ACTIVE = 5;
    public const REDUCED_DAILY_LIMIT = 1;

    public const LATE_THRESHOLD_REDUCE = 3;
    public const LATE_THRESHOLD_SUSPEND = 5;
    public const SUSPEND_DURATION_DAYS = 7;

    // ==================== Relasi ====================

    public function designer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'designer_id');
    }

    // ==================== Helper ====================

    public function isAvailable(): bool
    {
        if ($this->availability_status !== self::STATUS_AVAILABLE) {
            return false;
        }

        if ($this->suspended_until && $this->suspended_until->isFuture()) {
            return false;
        }

        return true;
    }

    public function isSuspended(): bool
    {
        return $this->availability_status === self::STATUS_SUSPENDED
            && $this->suspended_until
            && $this->suspended_until->isFuture();
    }

    public function isSuspendExpired(): bool
    {
        return $this->availability_status === self::STATUS_SUSPENDED
            && $this->suspended_until
            && $this->suspended_until->isPast();
    }
}