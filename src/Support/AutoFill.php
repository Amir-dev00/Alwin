<?php

namespace App\Support;

use Illuminate\Support\Str;

class AutoFill
{
    public static function slug(string $text, string $fallback = 'item'): string
    {
        $slug = Str::slug($text, '-', 'fa');

        return $slug !== '' ? $slug : $fallback.'-'.time();
    }

    public static function latinKey(string $text, string $fallback = 'upvc'): string
    {
        $ascii = Str::ascii($text);
        $key = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '_', $ascii) ?? ''));
        $key = trim($key, '_');

        return $key !== '' ? Str::limit($key, 64, '') : $fallback;
    }

    public static function seoTitle(string $name, string $brand = 'آلوین'): string
    {
        $name = trim($name);
        if ($name === '') {
            return $brand;
        }
        if (str_contains($name, $brand) || str_contains(strtoupper($name), 'ALWIN')) {
            return Str::limit($name, 70, '');
        }

        return Str::limit($name.' | '.$brand, 70, '');
    }

    public static function seoDescription(?string $text, string $fallback = ''): ?string
    {
        $plain = trim(preg_replace('/\s+/', ' ', strip_tags((string) $text)) ?? '');
        if ($plain === '') {
            $plain = trim($fallback);
        }

        return $plain !== '' ? Str::limit($plain, 160, '') : null;
    }

    public static function productType(?string $categoryName, ?string $current = null): ?string
    {
        if (filled($current)) {
            return $current;
        }
        $name = (string) $categoryName;
        if (str_contains($name, 'توری')) {
            return 'window_screen';
        }
        if (str_contains($name, 'در')) {
            return 'door_or_window';
        }

        return $name !== '' ? 'window' : null;
    }
}
