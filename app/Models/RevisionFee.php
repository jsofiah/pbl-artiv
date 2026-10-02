<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevisionFee extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'revision_fees';

    protected $fillable = [
        'name',
        'fee',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'fee' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    // ==================== Scope ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}