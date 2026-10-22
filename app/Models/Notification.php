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
    public const TYPE_NEW_CHAT = 'new_chat';
    public const TYPE_PAYMENT = 'payment';
    public const TYPE_SYSTEM = 'system_activity';

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

    // Gaya ikon per tipe, dipakai popup dan halaman Notifikasi
    public function uiIcon(): array
    {
        $check   = 'm4.5 12.75 6 6 9-13.5';
        $plus    = 'M12 4.5v15m7.5-7.5h-15';
        $refresh = 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99';
        $coin    = 'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z';
        $warning = 'M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z';
        $bell    = 'M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0';

        return match ($this->type) {
            self::TYPE_ORDER_COMPLETED, self::TYPE_REFUND_COMPLETED => ['bg-[#D5FC55]/60 text-slate-900', $check],
            self::TYPE_NEW_ORDER                                    => ['bg-violet-100 text-[#5B21B6]', $plus],
            self::TYPE_REVISION_REQUESTED                           => ['bg-amber-100 text-amber-700', $refresh],
            self::TYPE_REFUND_REQUESTED, self::TYPE_PAYMENT         => ['bg-amber-100 text-amber-700', $coin],
            self::TYPE_SUSPENSION, self::TYPE_CANNOT_CONTINUE,
            self::TYPE_REASSIGNMENT                                 => ['bg-red-100 text-red-600', $warning],
            self::TYPE_SYSTEM                                       => ['bg-slate-100 text-slate-700', $warning],
            default                                                 => ['bg-violet-100 text-[#5B21B6]', $bell],
        };
    }
}