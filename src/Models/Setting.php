<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\SiteCache;

class Setting extends Model
{
    protected $fillable = ['group', 'key', 'label', 'type', 'value'];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $row = static::query()->where('key', $key)->first();
        return $row?->value ?? $default;
    }

    public static function map(?string $group = null): array
    {
        $q = static::query();
        if ($group) {
            $q->where('group', $group);
        }
        return $q->pluck('value', 'key')->all();
    }

    protected static function booted(): void
    {
        static::saved(fn () => SiteCache::flush());
        static::deleted(fn () => SiteCache::flush());
    }
}
