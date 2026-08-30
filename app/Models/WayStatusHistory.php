<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[\Illuminate\Database\Eloquent\Attributes\Fillable(['way_id', 'status', 'remark', 'changed_by'])]
class WayStatusHistory extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (WayStatusHistory $model) {
            if (is_null($model->created_at)) {
                $model->created_at = Carbon::now();
            }
        });
    }

    public function way(): BelongsTo
    {
        return $this->belongsTo(Way::class);
    }
}
