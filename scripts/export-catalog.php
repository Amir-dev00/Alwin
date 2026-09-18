<?php

$root = dirname(__DIR__);
$db = new PDO('sqlite:'.$root.'/database/database.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$media = [];
foreach ($db->query('SELECT id, disk, path FROM media') as $row) {
    $media[(int) $row['id']] = $row;
}

$categories = [];
foreach ($db->query('SELECT id, name FROM product_categories') as $row) {
    $categories[(int) $row['id']] = $row['name'];
}

$products = [];
foreach ($db->query('SELECT * FROM products WHERE deleted_at IS NULL ORDER BY sort_order, id') as $row) {
    $close = $media[(int) $row['image_close_id']] ?? null;
    $open = $media[(int) $row['image_open_id']] ?? null;
    $products[] = [
        'title' => $row['name'],
        'product_type' => $row['product_type'],
        'category' => $categories[(int) $row['category_id']] ?? 'درب و پنجره UPVC',
        'images' => [
            'close' => $close['path'] ?? '',
            'open' => $open['path'] ?? '',
            'alt' => $row['alt_text'] ?: $row['name'],
            'folder_number' => (int) ($row['folder_number'] ?: $row['sort_order']),
        ],
    ];
}

$projects = [];
foreach ($db->query('SELECT * FROM projects WHERE deleted_at IS NULL ORDER BY sort_order, id') as $row) {
    $img = $media[(int) $row['image_id']] ?? null;
    $projects[] = [
        'id' => (int) $row['id'],
        'slug' => $row['slug'],
        'title' => $row['title'],
        'category' => $row['type'],
        'categoryLabel' => $row['type_label'],
        'image' => $img['path'] ?? '',
        'imageAlt' => $row['alt_text'] ?: $row['title'],
        'client_name' => $row['client_name'],
        'location' => $row['location'],
        'order' => (int) $row['sort_order'],
    ];
}

$dir = $root.'/data';
if (! is_dir($dir)) {
    mkdir($dir, 0755, true);
}
file_put_contents($dir.'/products-data.json', json_encode(['products' => $products], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
file_put_contents($dir.'/projects-data.json', json_encode(['projects' => $projects], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
echo 'products='.count($products).' projects='.count($projects).PHP_EOL;
