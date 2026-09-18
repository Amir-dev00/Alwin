<?php

return [

    'disk' => env('IMAGE_DISK', 'public'),

    'format' => env('IMAGE_FORMAT', 'webp'),

    'quality' => (int) env('IMAGE_QUALITY', 80),

    'thumbnail_quality' => (int) env('IMAGE_THUMBNAIL_QUALITY', 75),

    'max_width' => (int) env('IMAGE_MAX_WIDTH', 1920),

    'max_height' => (int) env('IMAGE_MAX_HEIGHT', 1920),

    'thumbnail_width' => (int) env('IMAGE_THUMBNAIL_WIDTH', 400),

    'max_kilobytes' => (int) env('IMAGE_MAX_KILOBYTES', 51200),

    'max_pixels' => (int) env('IMAGE_MAX_PIXELS', 40000000),

    'collections' => [
        'products' => 'product',
        'projects' => 'project',
        'articles' => 'article',
        'settings' => 'setting',
        'partners' => 'partner',
        'media' => 'media',
    ],

    'thumbnails' => [
        'products',
        'projects',
        'articles',
        'partners',
        'media',
    ],

    'mimes' => [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/bmp',
        'image/x-ms-bmp',
        'image/webp',
        'image/heic',
        'image/heif',
        'image/tiff',
        'image/tiff-fx',
    ],

    'extensions' => [
        'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'heic', 'heif', 'tif', 'tiff',
    ],

];
