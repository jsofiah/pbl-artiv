<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'products';

    protected $fillable = [
        'name',
        'description',
        'thumbnail_url',
        'price',
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

    public function tiers(): HasMany
    {
        return $this->hasMany(ProductTier::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function galleryItems(): HasMany
    {
        return $this->hasMany(GalleryItem::class);
    }

    // ==================== Scope ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ==================== Helper ====================

    public function hasTiers(): bool
    {
        return $this->tiers()->exists();
    }
}