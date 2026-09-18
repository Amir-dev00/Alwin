<?php

namespace App\Models;

use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category_id', 'name', 'slug', 'product_type', 'short_description', 'description',
        'specifications', 'image_close_id', 'image_open_id', 'alt_text', 'folder_number',
        'sort_order', 'show_on_home', 'show_on_listing', 'status', 'seo_title', 'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'specifications' => 'array',
            'show_on_home' => 'boolean',
            'show_on_listing' => 'boolean',
            'folder_number' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            if (! $model->slug) {
                $model->slug = Str::slug($model->name, '-', 'fa') ?: 'product-'.$model->id;
            }
            $model->description = HtmlSanitizer::clean($model->description);
            $model->short_description = HtmlSanitizer::clean($model->short_description);
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function imageClose(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_close_id');
    }

    public function imageOpen(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_open_id');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function scopeListed(Builder $query): Builder
    {
        return $query->where('show_on_listing', true);
    }

    public function toPublicArray(): array
    {
        $close = $this->imageClose?->publicUrl();
        $open = $this->imageOpen?->publicUrl() ?: $close;

        return [
            'id' => $this->id,
            'title' => $this->name,
            'slug' => $this->slug,
            'category' => $this->category?->name,
            'product_type' => $this->product_type,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'specifications' => $this->specifications ?: [],
            'show_on_home' => $this->show_on_home,
            'order' => $this->sort_order,
            'images' => [
                'close' => $close,
                'open' => $open,
                'main' => $close,
                'alt' => $this->alt_text ?: $this->name,
                'folder_number' => $this->folder_number,
            ],
            'seo' => [
                'title' => $this->seo_title,
                'description' => $this->seo_description,
            ],
        ];
    }
}
