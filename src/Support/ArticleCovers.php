<?php

namespace App\Support;

use App\Models\Article;
use Illuminate\Support\Facades\File;

final class ArticleCovers
{
    /**
     * @return array<string, string>
     */
    public static function mapping(): array
    {
        $file = base_path('images'.DIRECTORY_SEPARATOR.'mapping.json');
        if (! is_file($file)) {
            return [];
        }
        $json = json_decode((string) file_get_contents($file), true);
        $map = [];
        foreach (($json['mapping'] ?? []) as $slug => $name) {
            $map[(string) $slug] = 'images/articles/'.$name;
        }

        return $map;
    }

    public static function aliasRelative(string $slug, string $extension = 'webp'): string
    {
        $ext = strtolower($extension) ?: 'webp';
        if ($ext === 'jpeg') {
            $ext = 'jpg';
        }

        return 'images/article-covers/'.md5($slug).'.'.$ext;
    }

    public static function resolve(Article $article): ?string
    {
        $candidates = [];
        foreach (['webp', 'jpg', 'jpeg', 'png'] as $ext) {
            $candidates[] = self::aliasRelative($article->slug, $ext);
        }

        $stored = self::normalize((string) $article->cover_path);
        if ($stored !== '') {
            $candidates[] = $stored;
        }

        $mapped = self::mapping()[$article->slug] ?? null;
        if (is_string($mapped) && $mapped !== '') {
            $candidates[] = $mapped;
        }

        $fromListing = self::listingCover($article->slug);
        if ($fromListing) {
            $candidates[] = $fromListing;
        }

        $guessed = self::guess($article->slug, (string) $article->title);
        if ($guessed) {
            $candidates[] = $guessed;
        }

        foreach (array_unique($candidates) as $relative) {
            if (self::absolute($relative)) {
                return $relative;
            }
        }

        return $stored !== '' ? $stored : null;
    }

    public static function absolute(?string $relative): ?string
    {
        $relative = self::normalize((string) $relative);
        if ($relative === '' || str_contains($relative, '..')) {
            return null;
        }
        foreach ([base_path($relative), public_path($relative)] as $abs) {
            $abs = str_replace('/', DIRECTORY_SEPARATOR, $abs);
            if (is_file($abs)) {
                return $abs;
            }
        }

        return null;
    }

    public static function mime(?string $relative): string
    {
        $ext = strtolower(pathinfo((string) $relative, PATHINFO_EXTENSION));

        return match ($ext) {
            'webp' => 'image/webp',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'jpg', 'jpeg' => 'image/jpeg',
            default => 'application/octet-stream',
        };
    }

    private static function listingCover(string $slug): ?string
    {
        $index = ArticleImporter::sourceRoot().DIRECTORY_SEPARATOR.'index.html';
        if (! is_file($index)) {
            return null;
        }
        $html = File::get($index);
        $encoded = preg_quote(rawurlencode($slug), '/');
        $plain = preg_quote($slug, '/');
        if (preg_match('/href="\.\/(?:'.$encoded.'|'.$plain.')\/index\.html"[\s\S]{0,800}?<img src="\.\.\/(images\/articles\/[^"]+)"/iu', $html, $m)) {
            return self::normalize($m[1]);
        }

        return null;
    }

    private static function guess(string $slug, string $title): ?string
    {
        $dir = base_path('images'.DIRECTORY_SEPARATOR.'articles');
        if (! is_dir($dir)) {
            return null;
        }
        $needles = array_values(array_filter([
            str_replace('-', ' ', $slug),
            $title,
            explode('-', $slug)[0] ?? '',
        ]));
        foreach (File::files($dir) as $file) {
            $name = $file->getFilename();
            foreach ($needles as $needle) {
                if ($needle !== '' && mb_stripos($name, mb_substr($needle, 0, 12)) !== false) {
                    return 'images/articles/'.$name;
                }
            }
        }

        return null;
    }

    private static function normalize(string $path): string
    {
        $path = html_entity_decode(trim($path), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $path = parse_url($path, PHP_URL_PATH) ?: $path;
        }
        $path = ltrim(str_replace('\\', '/', rawurldecode($path)), '/');
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }

        return $path;
    }
}
