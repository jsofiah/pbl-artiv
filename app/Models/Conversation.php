<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'conversations';

    protected $fillable = [
        'order_id',
        'customer_id',
        'designer_id',
        'status',
    ];

    // ==================== Konstanta ====================

    public const STATUS_ACTIVE = 'active';
    public const STATUS_CLOSED = 'closed';

    // ==================== Relasi ====================

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function designer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'designer_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    // ==================== Helper ====================

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function unreadCountFor(User $user): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->whereNull('read_at')
            ->count();
    }
}