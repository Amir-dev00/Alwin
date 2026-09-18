<?php

namespace App\Support;

use App\Models\Article;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class ArticleImporter
{
    public static function sourceRoot(): string
    {
        $candidates = [
            base_path('data'.DIRECTORY_SEPARATOR.'articles'),
            base_path('articles'),
            public_path('articles-src'),
            base_path('database'.DIRECTORY_SEPARATOR.'articles-src'),
        ];
        foreach ($candidates as $dir) {
            if (is_dir($dir)) {
                return $dir;
            }
        }

        return $candidates[0];
    }

    public static function import(?string $root = null): int
    {
        $root = $root ?: self::sourceRoot();
        if (! is_dir($root)) {
            return 0;
        }

        $count = 0;
        $listingCovers = self::listingCovers($root);
        foreach (File::directories($root) as $dir) {
            $base = basename($dir);
            if (in_array($base, ['page', 'pages'], true) || str_starts_with($base, '.')) {
                continue;
            }
            $file = $dir.DIRECTORY_SEPARATOR.'index.html';
            if (! is_file($file)) {
                continue;
            }
            $parsed = self::parseFile($file, $base, $listingCovers[$base] ?? null);
            if (! $parsed['title']) {
                continue;
            }
            Article::withoutEvents(function () use ($parsed, &$count) {
                $article = Article::query()->firstOrNew(['slug' => $parsed['slug']]);
                if ($article->exists) {
                    return;
                }
                $article->fill($parsed)->save();
                $count++;
            });
        }

        return $count;
    }

    public static function parseFile(string $file, string $slug, ?string $listingCover = null): array
    {
        $html = File::get($file);
        $title = self::match($html, '/<h1[^>]*class="article-detail__title"[^>]*>(.*?)<\/h1>/is')
            ?: self::match($html, '/<title>(.*?)<\/title>/is');
        $title = html_entity_decode(trim(strip_tags((string) $title)), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $excerpt = self::namedMeta($html, 'description');
        $excerpt = html_entity_decode(trim((string) $excerpt), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $cover = self::propertyMeta($html, 'og:image');
        if ($cover && str_contains($cover, '://')) {
            $cover = parse_url($cover, PHP_URL_PATH) ?: $cover;
        }
        $cover = self::normalizeCover($cover)
            ?: self::mappedCover($slug)
            ?: self::normalizeCover($listingCover)
            ?: self::guessCover($slug, $title);

        $published = self::propertyMeta($html, 'article:published_time');
        $author = self::propertyMeta($html, 'article:author') ?: 'آلوین';

        $body = '';
        if (preg_match('/<div class="article-content">(.*?)<p class="article-back">/is', $html, $m)) {
            $body = trim($m[1]);
        } elseif (preg_match('/<div class="article-content">(.*?)<\/div>/is', $html, $m)) {
            $body = trim($m[1]);
        }

        return [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $excerpt !== '' ? $excerpt : Str::limit($title, 160, '…'),
            'body' => $body,
            'cover_path' => $cover,
            'cover_alt' => $title,
            'author' => $author,
            'published_at' => $published ?: now(),
            'status' => 'published',
            'seo_title' => $title,
            'seo_description' => Str::limit($excerpt !== '' ? $excerpt : $title, 320, ''),
        ];
    }

    private static function match(string $html, string $pattern): ?string
    {
        return preg_match($pattern, $html, $m) ? trim($m[1]) : null;
    }

    private static function namedMeta(string $html, string $name): ?string
    {
        if (preg_match('/<meta\b[^>]*name="'.preg_quote($name, '/').'"[^>]*content="([^"]*)"/i', $html, $m)) {
            return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return null;
    }

    private static function propertyMeta(string $html, string $property): ?string
    {
        if (preg_match('/<meta\b[^>]*property="'.preg_quote($property, '/').'"[^>]*content="([^"]*)"/i', $html, $m)) {
            return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        return null;
    }

    /**
     * @return array<string, string>
     */
    private static function listingCovers(string $root): array
    {
        $index = $root.DIRECTORY_SEPARATOR.'index.html';
        if (! is_file($index)) {
            return [];
        }

        $html = File::get($index);
        $map = [];
        if (preg_match_all('/href="\.\/([^"]+)\/index\.html"[\s\S]{0,800}?<img src="\.\.\/(images\/articles\/[^"]+)"/iu', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $slug = rawurldecode($match[1]);
                $map[$slug] = $match[2];
            }
        }

        return $map;
    }

    private static function mappedCover(string $slug): ?string
    {
        $mapped = ArticleCovers::mapping()[$slug] ?? null;

        return is_string($mapped) ? self::existingCover($mapped) : null;
    }

    private static function normalizeCover(?string $cover): ?string
    {
        if (! $cover) {
            return null;
        }
        $cover = html_entity_decode($cover, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if (str_contains($cover, '://')) {
            $cover = parse_url($cover, PHP_URL_PATH) ?: $cover;
        }
        $cover = ltrim(str_replace('\\', '/', rawurldecode($cover)), '/');
        return self::existingCover($cover);
    }

    private static function guessCover(string $slug, string $title): ?string
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

    private static function existingCover(string $relative): ?string
    {
        $relative = ltrim(str_replace('\\', '/', $relative), '/');
        $abs = base_path(str_replace('/', DIRECTORY_SEPARATOR, $relative));
        if (is_file($abs)) {
            return $relative;
        }
        $decoded = rawurldecode($relative);
        $abs = base_path(str_replace('/', DIRECTORY_SEPARATOR, $decoded));

        return is_file($abs) ? $decoded : null;
    }
}
