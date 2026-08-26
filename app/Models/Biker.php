<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name'])]
class Biker extends Model
{
    public function ways()
    {
        return $this->hasMany(Way::class);
    }

    protected function casts(): array
    {
        return [];
    }
}