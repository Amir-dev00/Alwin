<?php

return [
    'api_url' => env('API_URL', '/api/v1'),
    'timezone' => env('APP_TIMEZONE', 'Asia/Tehran'),
    'cron_secret' => env('CRON_SECRET', ''),
    'version' => env('APP_VERSION', '1.0.0'),
    'build_date' => env('APP_BUILD_DATE'),
    'git_commit' => env('APP_GIT_COMMIT'),
];
