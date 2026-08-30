<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'shop_id', 'biker_id', 'assigned_at', 'item_image', 'amount', 'delivery_fees', 'recipient_name',
    'address', 'phone_number', 'date', 'remark', 'status',
])]
class Way extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_ONWAY = 'onway';
    public const STATUS_FAILED = 'failed';
    public const STATUS_DELIVERED = 'delivered';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_ONWAY,
        self::STATUS_FAILED,
        self::STATUS_DELIVERED,
    ];

    public function shop()
    {
        return $this->belongsTo(User::class, 'shop_id');
    }

    public function biker()
    {
        return $this->belongsTo(Biker::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(WayStatusHistory::class)->orderByDesc('created_at');
    }

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'amount' => 'decimal:2',
            'delivery_fees' => 'decimal:2',
            'date' => 'date',
        ];
    }
}