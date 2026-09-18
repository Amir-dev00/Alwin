<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'alt_text', 'image_id', 'url', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function toPublicArray(): array
    {
        return [
            'name' => $this->name,
            'alt' => $this->alt_text ?: $this->name,
            'image' => $this->image?->publicUrl(),
            'url' => $this->url,
            'order' => $this->sort_order,
        ];
    }
}
