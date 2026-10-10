<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'reviews';

    // ==================== Konstanta (dipakai view & controller) ====================

    public const ASPECTS = [
        'komunikasi'  => ['label' => 'Komunikasi Cepat',          'icon' => '⚡'],
        'kualitas'    => ['label' => 'Kualitas Desain Memuaskan', 'icon' => '🪄'],
        'tepat_waktu' => ['label' => 'Tepat Waktu',               'icon' => '🕒'],
    ];

    public const RATING_LABELS = [
        1 => 'Sangat Kurang',
        2 => 'Kurang Puas',
        3 => 'Cukup',
        4 => 'Puas',
        5 => 'Luar Biasa / Sangat Puas',
    ];

    protected $fillable = [
        'order_id',
        'customer_id',
        'designer_id',
        'rating',
        'aspects',
        'comment',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'aspects' => 'array',
            'is_public' => 'boolean',
        ];
    }

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

    // ==================== Scope ====================

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    // ==================== Helper ====================

    public function isPositive(): bool
    {
        return $this->rating >= 4;
    }
}