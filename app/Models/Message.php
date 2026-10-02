<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Message extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'messages';

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'type',
        'body',
        'is_flagged',
        'flag_reason',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_flagged' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    // ==================== Konstanta Type ====================

    public const TYPE_TEXT = 'text';
    public const TYPE_FILE = 'file';
    public const TYPE_DELIVERABLE = 'deliverable';
    public const TYPE_SYSTEM = 'system';

    // ==================== Relasi ====================

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class);
    }

    public function deliverable(): HasOne
    {
        return $this->hasOne(Deliverable::class);
    }

    // ==================== Helper ====================

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function isDeliverable(): bool
    {
        return $this->type === self::TYPE_DELIVERABLE;
    }

    public function isSystem(): bool
    {
        return $this->type === self::TYPE_SYSTEM;
    }

    public function markAsRead(): void
    {
        if (!$this->isRead()) {
            $this->update(['read_at' => now()]);
        }
    }

    public function flag(string $reason): void
    {
        $this->update([
            'is_flagged' => true,
            'flag_reason' => $reason,
        ]);
    }
}