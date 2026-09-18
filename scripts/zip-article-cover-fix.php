<?php

$base = dirname(__DIR__);
$zipPath = $base.DIRECTORY_SEPARATOR.'alwin-article-covers-fix.zip';
if (is_file($zipPath)) {
    unlink($zipPath);
}

$zip = new ZipArchive;
if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
    fwrite(STDERR, "Could not create zip\n");
    exit(1);
}

$files = [
    'src/Support/ArticleCovers.php',
    'src/Models/Article.php',
    'src/Controllers/Web/ArticleController.php',
    'src/Support/ArticleImporter.php',
    'includes/web.php',
    'images/mapping.json',
];
foreach ($files as $rel) {
    $abs = $base.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $rel);
    if (! is_file($abs)) {
        fwrite(STDERR, "Missing $rel\n");
        continue;
    }
    $zip->addFile($abs, $rel);
}

$coverDir = $base.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'article-covers';
foreach (scandir($coverDir) as $name) {
    if ($name === '.' || $name === '..') {
        continue;
    }
    $zip->addFile($coverDir.DIRECTORY_SEPARATOR.$name, 'images/article-covers/'.$name);
}

$zip->close();
echo $zipPath.' ('.round(filesize($zipPath) / 1024).' KB)'.PHP_EOL;
