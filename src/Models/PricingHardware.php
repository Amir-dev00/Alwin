<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingHardware extends Model
{
    protected $table = 'pricing_hardwares';

    protected $fillable = ['key', 'name', 'price_turk', 'price_germany', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'price_turk' => 'integer',
            'price_germany' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
