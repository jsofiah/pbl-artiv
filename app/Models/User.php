<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUuids;

    protected $table = 'users';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'google_id',
        'username',
        'email',
        'full_name',
        'phone',
        'avatar_url',
        'role',
        'password',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // ==================== Konstanta Role ====================

    public const ROLE_CUSTOMER = 'customer';
    public const ROLE_DESIGNER = 'designer';
    public const ROLE_ADMIN = 'admin';


    public function home(): string
    {
        return match ($this->role) {
            'designer' => route('designer.dashboard'),
            'admin'    => route('admin.dashboard'),
            default    => route('customer.beranda'),
        };
    }
    // ==================== Relasi ====================

    // Designer
    public function designerStats(): HasOne
    {
        return $this->hasOne(DesignerStat::class, 'designer_id');
    }

    // Order
    public function customerOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function designerOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'designer_id');
    }

    // Chat
    public function conversationsAsCustomer(): HasMany
    {
        return $this->hasMany(Conversation::class, 'customer_id');
    }

    public function conversationsAsDesigner(): HasMany
    {
        return $this->hasMany(Conversation::class, 'designer_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    // Review
    public function reviewsAsCustomer(): HasMany
    {
        return $this->hasMany(Review::class, 'customer_id');
    }

    public function reviewsAsDesigner(): HasMany
    {
        return $this->hasMany(Review::class, 'designer_id');
    }

    // Refund
    public function refundsAsCustomer(): HasMany
    {
        return $this->hasMany(OrderRefund::class, 'customer_id');
    }

    public function processedRefunds(): HasMany
    {
        return $this->hasMany(OrderRefund::class, 'processed_by');
    }

    // Lain-lain
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }

    public function orderSplits(): HasMany
    {
        return $this->hasMany(OrderSplit::class, 'designer_id');
    }

    public function orderLogs(): HasMany
    {
        return $this->hasMany(OrderLog::class, 'actor_id');
    }

    // ==================== Helper ====================

    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    public function isDesigner(): bool
    {
        return $this->role === self::ROLE_DESIGNER;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }
}