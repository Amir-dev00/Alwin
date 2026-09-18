<?php

namespace App\Models;

use App\Services\ImageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    use SoftDeletes;

    protected $table = 'media';

    protected $fillable = [
        'disk', 'path', 'filename', 'original_name', 'mime', 'size',
        'kind', 'alt', 'width', 'height', 'uploaded_by',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function publicUrl(): string
    {
        if ($this->disk === 'site') {
            return '/'.ltrim(str_replace('\\', '/', $this->path), '/');
        }

        return url('/storage/'.ltrim(str_replace('\\', '/', $this->path), '/'));
    }

    public function thumbnailUrl(): string
    {
        if ($this->kind !== 'image' || $this->disk === 'site') {
            return $this->publicUrl();
        }

        return app(ImageService::class)->thumbnailUrl($this->path);
    }

    public function guessCollection(): string
    {
        $path = str_replace('\\', '/', (string) $this->path);
        foreach (['products', 'projects', 'articles', 'settings', 'partners', 'media'] as $folder) {
            if (str_starts_with($path, $folder.'/')) {
                return $folder;
            }
        }
        if (Product::withTrashed()->where(function ($q) {
            $q->where('image_close_id', $this->id)->orWhere('image_open_id', $this->id);
        })->exists()) {
            return 'products';
        }
        if (Project::withTrashed()->where('image_id', $this->id)->exists()) {
            return 'projects';
        }
        if (Partner::withTrashed()->where('image_id', $this->id)->exists()) {
            return 'partners';
        }

        return 'media';
    }

    public function isUsed(): bool
    {
        $id = $this->id;
        $inProducts = Product::withTrashed()
            ->where(function ($q) use ($id) {
                $q->where('image_close_id', $id)->orWhere('image_open_id', $id);
            })
            ->exists();

        return $inProducts
            || Project::withTrashed()->where('image_id', $id)->exists()
            || Partner::withTrashed()->where('image_id', $id)->exists();
    }
}
