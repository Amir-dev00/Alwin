<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingModel extends Model
{
    protected $fillable = [
        'catalog_id',
        'name',
        'tab',
        'category_key',
        'family',
        'glass_deduction',
        'hardware_qty',
        'hardware_mode',
        'hardware_type_key',
        'lites',
        'sashes',
        'transom_top',
        'transom_bottom',
        'panel_ratio',
        'area_rate',
        'image',
        'recipe',
        'recipe_locked',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'glass_deduction' => 'float',
            'hardware_qty' => 'integer',
            'lites' => 'integer',
            'sashes' => 'integer',
            'transom_top' => 'boolean',
            'transom_bottom' => 'boolean',
            'panel_ratio' => 'float',
            'area_rate' => 'integer',
            'recipe' => 'array',
            'recipe_locked' => 'boolean',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'catalog_id' => 'integer',
        ];
    }
}
