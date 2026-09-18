<?php

use App\Middleware\EnsureSuperAdmin;
use App\Middleware\EnsureUserIsActive;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

$base = dirname(__DIR__);

$app = Application::configure(basePath: $base)
    ->withRouting(
        web: $base.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'web.php',
        api: $base.DIRECTORY_SEPARATOR.'api'.DIRECTORY_SEPARATOR.'routes.php',
        commands: $base.DIRECTORY_SEPARATOR.'includes'.DIRECTORY_SEPARATOR.'console.php',
        health: '/up',
        apiPrefix: 'api/v1',
        then: function () use ($base): void {
            Route::middleware('api')->prefix('api')->group($base.DIRECTORY_SEPARATOR.'api'.DIRECTORY_SEPARATOR.'routes.php');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.active' => EnsureUserIsActive::class,
            'admin.super' => EnsureSuperAdmin::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('admin.login', absolute: false));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard', absolute: false));
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

$app->useAppPath($base.DIRECTORY_SEPARATOR.'src');
$app->usePublicPath($base);
$app->useDatabasePath($base.DIRECTORY_SEPARATOR.'database');
$app->useStoragePath($base.DIRECTORY_SEPARATOR.'storage');

return $app;
