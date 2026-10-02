<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductTier extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'product_tiers';

    protected $fillable = [
        'product_id',
        'name',
        'price',
        'description',
        'thumbnail_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    // ==================== Relasi ====================

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    // ==================== Scope ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}