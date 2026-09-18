<?php

namespace App\Models;

use App\Services\ImageService;
use App\Support\ArticleCovers;
use App\Support\HtmlSanitizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Article extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'cover_path', 'cover_alt',
        'author', 'published_at', 'status', 'show_on_listing', 'seo_title', 'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'show_on_listing' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $model) {
            if (! $model->slug) {
                $model->slug = Str::slug($model->title, '-', 'fa') ?: 'article-'.time();
            }
            if ($model->isDirty('body')) {
                $model->body = HtmlSanitizer::cleanArticle($model->body);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(function (Builder $q) {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }

    public function scopeListed(Builder $query): Builder
    {
        return $query->where('show_on_listing', true);
    }

    public function isPublished(): bool
    {
        return $this->status === 'published'
            && ($this->published_at === null || $this->published_at->lte(now()));
    }

    public function coverUrl(): ?string
    {
        $path = $this->resolvedCoverPath();
        if (! $path) {
            return null;
        }
        if ($this->isStoredCover($path)) {
            return app(ImageService::class)->url($path);
        }
        if (str_starts_with($path, 'images/article-covers/') || str_starts_with($path, 'images/articles/')) {
            return '/article-covers/'.$this->id;
        }

        return '/'.implode('/', array_map('rawurlencode', explode('/', $path)));
    }

    public function coverThumbUrl(): ?string
    {
        $path = $this->resolvedCoverPath();
        if (! $path) {
            return null;
        }
        if ($this->isStoredCover($path)) {
            return app(ImageService::class)->thumbnailUrl($path);
        }

        return $this->coverUrl();
    }

    public function resolvedCoverPath(): ?string
    {
        return ArticleCovers::resolve($this);
    }

    private function isStoredCover(string $path): bool
    {
        foreach (['articles/', 'products/', 'projects/', 'settings/', 'partners/', 'media/'] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }

    public function publishedLabel(): string
    {
        if (! $this->published_at) {
            return '';
        }

        return $this->published_at->locale('fa')->translatedFormat('j F Y');
    }

    public function toPublicArray(bool $withBody = false): array
    {
        $data = [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'cover' => $this->coverUrl(),
            'cover_alt' => $this->cover_alt ?: $this->title,
            'author' => $this->author,
            'published_at' => $this->published_at?->toIso8601String(),
            'published_label' => $this->publishedLabel(),
            'seo' => [
                'title' => $this->seo_title ?: $this->title,
                'description' => $this->seo_description ?: $this->excerpt,
            ],
        ];
        if ($withBody) {
            $data['body'] = $this->body;
        }

        return $data;
    }
}
