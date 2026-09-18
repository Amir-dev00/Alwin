<?php

namespace App\Models;

use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'type', 'type_label', 'summary', 'description',
        'image_id', 'alt_text', 'client_name', 'location', 'sort_order',
        'show_on_home', 'show_on_listing', 'status', 'seo_title', 'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'show_on_home' => 'boolean',
            'show_on_listing' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            if (! $model->slug) {
                $model->slug = Str::slug($model->title, '-', 'fa');
            }
            $model->description = HtmlSanitizer::clean($model->description);
            $model->summary = HtmlSanitizer::clean($model->summary);
        });
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function scopeListed(Builder $query): Builder
    {
        return $query->where('show_on_listing', true);
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function toPublicArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'image' => $this->image?->publicUrl(),
            'imageAlt' => $this->alt_text ?: $this->title,
            'location' => $this->location,
            'client_name' => $this->client_name,
            'order' => $this->sort_order,
            'category' => $this->type,
            'categoryLabel' => $this->type_label,
            'summary' => $this->summary,
            'description' => $this->description,
            'show_on_home' => $this->show_on_home,
            'seo' => [
                'title' => $this->seo_title,
                'description' => $this->seo_description,
            ],
        ];
    }
}
