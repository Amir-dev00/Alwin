<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingBrand extends Model
{
    protected $fillable = ['key', 'name', 'material', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function prices(): HasMany
    {
        return $this->hasMany(PricingBrandPrice::class, 'brand_id');
    }

    public function priceMap(): array
    {
        return $this->prices()
            ->get()
            ->mapWithKeys(fn (PricingBrandPrice $row) => [$row->component_key => $row->price])
            ->all();
    }
}
