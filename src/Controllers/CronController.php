<?php

namespace App\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class CronController extends Controller
{
    public function __invoke(string $token): JsonResponse
    {
        $secret = (string) config('alwin.cron_secret');
        abort_unless($secret !== '' && hash_equals($secret, $token), 404);

        Artisan::call('schedule:run');

        return response()->json([
            'ok' => true,
            'timezone' => config('app.timezone'),
        ]);
    }
}
