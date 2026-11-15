<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MessageAttachment extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'message_attachments';

    protected $fillable = [
        'message_id',
        'file_url',
        'file_name',
        'file_size',
        'mime_type',
        'file_type',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    // ==================== Relasi ====================

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function galleryItem(): HasOne
    {
        return $this->hasOne(GalleryItem::class, 'message_attachment_id');
    }

    // ==================== Scope ====================
    public function scopeGalleryCandidate(Builder $q): Builder
    {
        return $q->where('mime_type', 'like', 'image/%')
            ->whereHas('message.conversation.order', function ($order) {
                $order->where('status', Order::STATUS_COMPLETED);
            })
            ->whereHas('message.deliverable', function ($deliverable) {
                $deliverable->where('status', 'approved');
            })
            ->whereDoesntHave('galleryItem');
    }




    // ==================== Helper ====================

    public function isImage(): bool
    {
        return $this->mime_type && str_starts_with($this->mime_type, 'image/');
    }

    public function humanSize(): string
    {
        $bytes = $this->file_size ?? 0;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}