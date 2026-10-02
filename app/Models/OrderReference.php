<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderReference extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'order_references';

    protected $fillable = [
        'order_id',
        'type',
        'file_url',
        'file_name',
        'file_size',
        'mime_type',
        'external_url',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    // ==================== Konstanta ====================

    public const TYPE_FILE = 'file';
    public const TYPE_LINK = 'link';

    // ==================== Relasi ====================

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // ==================== Helper ====================

    public function isFile(): bool
    {
        return $this->type === self::TYPE_FILE;
    }

    public function isLink(): bool
    {
        return $this->type === self::TYPE_LINK;
    }
}