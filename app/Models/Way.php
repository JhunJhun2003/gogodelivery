<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'shop_id', 'biker_id', 'item_image', 'amount', 'delivery_fees', 'recipient_name',
    'address', 'phone_number', 'date', 'remark', 'status',
])]
class Way extends Model
{
    public function shop()
    {
        return $this->belongsTo(User::class, 'shop_id');
    }

    public function biker()
    {
        return $this->belongsTo(Biker::class);
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'delivery_fees' => 'decimal:2',
            'date' => 'date',
        ];
    }
}