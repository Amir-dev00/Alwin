<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

final class SiteCache
{
    public const KEYS = [
        'api.site',
        'api.products',
        'api.projects',
        'api.home',
        'api.pricing',
    ];

    public static function flush(): void
    {
        foreach (self::KEYS as $key) {
            Cache::forget($key);
        }
        Cache::forget('site.settings');
        Cache::forget('site.nav.header');
        Cache::forget('site.nav.footer');
        foreach (['home', 'about', 'services', 'portfolio', 'contact', 'project', 'articles'] as $page) {
            Cache::forget('site.page.'.$page);
        }
    }
}
