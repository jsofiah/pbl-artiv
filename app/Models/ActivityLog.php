<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

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

    // ==================== Helper ====================

    public static function record(string $action, string $description, ?string $targetType = null, ?string $targetId = null): void
    {
        // Kolom target_id bertipe uuid, jadi nilai non-UUID dikosongkan agar insert tidak gagal
        if ($targetId && ! Str::isUuid($targetId)) {
            $targetId = null;
        }

        static::create([
            'user_id'     => auth()->id(),
            'action'      => $action,
            'target_type' => $targetType,
            'target_id'   => $targetId,
            'description' => $description,
        ]);
    }
}