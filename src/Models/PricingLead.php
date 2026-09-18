<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingLead extends Model
{
    protected $fillable = [
        'model_id',
        'model_name',
        'name',
        'phone',
        'width_cm',
        'height_cm',
        'quantity',
        'profile_key',
        'glass_key',
        'hardware_origin',
        'hardware_type_key',
        'estimate',
        'notes',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'model_id' => 'integer',
            'width_cm' => 'integer',
            'height_cm' => 'integer',
            'quantity' => 'integer',
            'estimate' => 'integer',
        ];
    }
}
