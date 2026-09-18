<?php

use Illuminate\Foundation\Application;
use Symfony\Component\Console\Input\ArgvInput;

define('LARAVEL_START', microtime(true));

require dirname(__DIR__).'/vendor/autoload.php';

/** @var Application $app */
$app = require dirname(__DIR__).'/includes/bootstrap.php';

exit($app->handleCommand(new ArgvInput(['artisan', 'schedule:run'])));
