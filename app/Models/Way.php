<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'shop_id', 'item_image', 'amount', 'delivery_fees', 'recipient_name',
    'address', 'phone_number', 'date', 'remark', 'status',
])]
class Way extends Model
{
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'delivery_fees' => 'decimal:2',
            'date' => 'date',
        ];
    }
}