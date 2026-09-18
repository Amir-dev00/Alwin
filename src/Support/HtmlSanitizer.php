<?php

namespace App\Support;

final class HtmlSanitizer
{
    private const ALLOWED_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u',
        'h2', 'h3', 'h4', 'ul', 'ol', 'li', 'a', 'blockquote', 'span',
    ];

    private const ARTICLE_TAGS = [
        'p', 'br', 'strong', 'b', 'em', 'i', 'u', 'h2', 'h3', 'h4',
        'ul', 'ol', 'li', 'a', 'blockquote', 'span', 'div', 'img',
        'figure', 'figcaption', 'hr', 'table', 'thead', 'tbody', 'tr', 'th', 'td',
    ];

    public static function clean(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html) ?? $html;
        $html = preg_replace('#<style\b[^>]*>.*?</style>#is', '', $html) ?? $html;
        $html = preg_replace('#on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html) ?? $html;

        $allowed = '<'.implode('><', self::ALLOWED_TAGS).'>';
        $html = strip_tags($html, $allowed);

        $html = preg_replace_callback('#<a\b([^>]*)>#i', function (array $m) {
            $attrs = $m[1];
            $href = '';
            if (preg_match('/href\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $attrs, $hm)) {
                $href = $hm[2] ?? $hm[3] ?? $hm[4] ?? '';
            }
            $href = trim($href);
            if ($href === '' || preg_match('#^\s*(javascript|data|vbscript):#i', $href)) {
                return '<a>';
            }
            return '<a href="'.e($href).'" rel="noopener noreferrer">';
        }, $html) ?? $html;

        return $html;
    }

    public static function cleanArticle(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        $html = preg_replace('#<script\b[^>]*>.*?</script>#is', '', $html) ?? $html;
        $html = preg_replace('#<style\b[^>]*>.*?</style>#is', '', $html) ?? $html;
        $html = preg_replace('#on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)#i', '', $html) ?? $html;

        $allowed = '<'.implode('><', self::ARTICLE_TAGS).'>';
        $html = strip_tags($html, $allowed);

        $html = preg_replace_callback('#<a\b([^>]*)>#i', function (array $m) {
            $attrs = $m[1];
            $href = '';
            if (preg_match('/href\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $attrs, $hm)) {
                $href = $hm[2] ?? $hm[3] ?? $hm[4] ?? '';
            }
            $href = trim($href);
            if ($href === '' || preg_match('#^\s*(javascript|data|vbscript):#i', $href)) {
                return '<a>';
            }

            return '<a href="'.e($href).'" rel="noopener noreferrer">';
        }, $html) ?? $html;

        $html = preg_replace_callback('#<img\b([^>]*)>#i', function (array $m) {
            $attrs = $m[1];
            $src = '';
            $alt = '';
            if (preg_match('/src\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $attrs, $sm)) {
                $src = $sm[2] ?? $sm[3] ?? $sm[4] ?? '';
            }
            if (preg_match('/alt\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $attrs, $am)) {
                $alt = $am[2] ?? $am[3] ?? $am[4] ?? '';
            }
            $src = trim($src);
            if ($src === '' || preg_match('#^\s*(javascript|data|vbscript):#i', $src)) {
                return '';
            }

            return '<img src="'.e($src).'" alt="'.e($alt).'" loading="lazy">';
        }, $html) ?? $html;

        return $html;
    }

    public static function plain(?string $html): string
    {
        return trim(html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}
