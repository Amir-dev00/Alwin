<?php

$root = dirname(__DIR__);
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
$uri = $uri === '' ? '/' : $uri;

$denied = [
    '#^/(includes|src|config|migrations|database|templates|vendor|bootstrap|tests|scripts|data|cms|_obsolete)(/|$)#',
    '#^/(\.env|\.git|composer\.(json|lock)|artisan|phpunit\.xml)#',
    '#^/storage/(framework|logs|pail)(/|$)#',
    '#^/storage/app/private(/|$)#',
];
foreach ($denied as $pattern) {
    if (preg_match($pattern, $uri)) {
        http_response_code(403);
        echo 'Forbidden';

        return true;
    }
}

$static = null;
if (preg_match('#^/storage/(.+)$#', $uri, $match)) {
    $candidate = $root.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $match[1]);
    if (is_file($candidate)) {
        $static = $candidate;
    }
} elseif ($uri !== '/') {
    $candidate = $root.str_replace('/', DIRECTORY_SEPARATOR, $uri);
    if (is_file($candidate)) {
        $static = $candidate;
    }
}

if ($static) {
    $real = realpath($static);
    $allowed = array_values(array_filter([
        realpath($root.DIRECTORY_SEPARATOR.'assets'),
        realpath($root.DIRECTORY_SEPARATOR.'images'),
        realpath($root.DIRECTORY_SEPARATOR.'public'),
        realpath($root.DIRECTORY_SEPARATOR.'storage'.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'public'),
        realpath($root.DIRECTORY_SEPARATOR.'robots.txt'),
    ]));
    $ok = $real && array_reduce($allowed, function (bool $carry, string $base) use ($real): bool {
        return $carry || $real === $base || str_starts_with($real, $base.DIRECTORY_SEPARATOR);
    }, false);
    if ($ok) {
        $ext = strtolower(pathinfo($real, PATHINFO_EXTENSION));
        $types = [
            'css' => 'text/css; charset=utf-8',
            'js' => 'application/javascript; charset=utf-8',
            'mjs' => 'application/javascript; charset=utf-8',
            'json' => 'application/json',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
            'gif' => 'image/gif',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'xml' => 'application/xml',
            'txt' => 'text/plain; charset=utf-8',
        ];
        header('Content-Type: '.($types[$ext] ?? 'application/octet-stream'));
        header('Content-Length: '.filesize($real));
        readfile($real);

        return true;
    }
}

require $root.DIRECTORY_SEPARATOR.'index.php';
