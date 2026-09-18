<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingBrandPrice extends Model
{
    protected $fillable = ['brand_id', 'component_key', 'price'];

    protected function casts(): array
    {
        return ['price' => 'integer'];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(PricingBrand::class, 'brand_id');
    }
}
