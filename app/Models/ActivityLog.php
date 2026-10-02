<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'activity_logs';

    protected $fillable = [
        'user_id',
        'action',
        'target_type',
        'target_id',
        'description',
    ];

    // ==================== Relasi ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}