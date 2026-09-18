<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Page extends Model
{
    protected $fillable = ['key', 'title', 'path', 'seo_title', 'seo_description'];

    public function blocks(): HasMany
    {
        return $this->hasMany(PageBlock::class)->orderBy('sort_order');
    }

    public function blockMap(): array
    {
        $out = [];
        foreach ($this->blocks as $block) {
            $out[$block->key] = $block->decodedValue();
        }
        return $out;
    }
}
