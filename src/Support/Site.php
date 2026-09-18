<?php

namespace App\Support;

use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class Site
{
    public static function settings(): array
    {
        return Cache::remember('site.settings', 30, fn () => Setting::map());
    }

    public static function setting(string $key, mixed $default = null): mixed
    {
        return self::settings()[$key] ?? $default;
    }

    public static function page(string $key): ?Page
    {
        return Cache::remember("site.page.{$key}", 30, fn () => Page::query()->with('blocks')->where('key', $key)->first());
    }

    public static function block(string $pageKey, string $blockKey, mixed $default = null): mixed
    {
        $page = self::page($pageKey);
        if (! $page) {
            return $default;
        }
        $map = $page->blockMap();

        return $map[$blockKey] ?? $default;
    }

    public static function nav(string $location): Collection
    {
        return Cache::remember("site.nav.{$location}", 30, function () use ($location) {
            return NavigationItem::query()
                ->where('location', $location)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(function (NavigationItem $item) {
                    $item->href = self::href($item->url);

                    return $item;
                });
        });
    }

    public static function href(?string $url): string
    {
        $url = trim((string) $url);
        if ($url === '' || $url === '/') {
            return route('home');
        }
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://') || str_starts_with($url, 'mailto:') || str_starts_with($url, 'tel:') || str_starts_with($url, '#')) {
            return $url;
        }

        $map = [
            'index.html' => 'home',
            '/index.html' => 'home',
            'about.html' => 'about',
            '/about.html' => 'about',
            'services.html' => 'products',
            '/services.html' => 'products',
            'portfolio.html' => 'projects.index',
            '/portfolio.html' => 'projects.index',
            'contact.html' => 'contact',
            '/contact.html' => 'contact',
            'articles/' => 'articles.index',
            '/articles/' => 'articles.index',
            'articles' => 'articles.index',
        ];
        if (isset($map[$url])) {
            return route($map[$url]);
        }
        if (str_starts_with($url, '/')) {
            return url($url);
        }

        return url('/'.ltrim($url, '/'));
    }

    public static function fa(int|string $value): string
    {
        return strtr((string) $value, [
            '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴',
            '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹',
        ]);
    }

    public static function phoneHref(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        return $digits ? 'tel:'.$digits : '#';
    }

    /**
     * @return list<string>
     */
    public static function lines(?string $value, ?string $fallback = null): array
    {
        $raw = trim((string) ($value !== null && $value !== '' ? $value : $fallback));
        if ($raw === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', preg_split('/\R/u', $raw) ?: [])));
    }

    public static function mediaUrl(?string $path, string $fallback): string
    {
        $path = trim((string) $path);
        if ($path === '') {
            return asset($fallback);
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, 'data:')) {
            return $path;
        }
        if (str_starts_with($path, '/storage/') || str_starts_with($path, 'storage/')) {
            return url('/'.ltrim($path, '/'));
        }
        if (str_starts_with($path, 'settings/') || str_starts_with($path, 'media/') || str_starts_with($path, 'partners/')) {
            return url('/storage/'.$path);
        }

        return asset(ltrim($path, '/'));
    }
}
