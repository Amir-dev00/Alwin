<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingGlass extends Model
{
    protected $table = 'pricing_glasses';

    protected $fillable = ['key', 'name', 'price_per_sqm', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'price_per_sqm' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
