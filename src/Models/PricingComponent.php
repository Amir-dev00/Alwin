<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingComponent extends Model
{
    protected $fillable = ['key', 'name', 'unit', 'sort_order'];

    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }
}
