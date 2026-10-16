<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlacklistKeyword extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'blacklist_keywords';
    protected $keyType = 'string';
    public $incrementing = false;
    

    protected $fillable = [
        'keyword',
        'type',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public const TYPE_LINK = 'link';
    public const TYPE_PHONE = 'phone';
    public const TYPE_EMAIL = 'email';
    public const TYPE_WORD = 'word';


    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }
}