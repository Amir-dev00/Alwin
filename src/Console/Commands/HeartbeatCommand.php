<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class HeartbeatCommand extends Command
{
    protected $signature = 'alwin:heartbeat';

    protected $description = 'Record that the single application scheduler ran';

    public function handle(): int
    {
        $path = storage_path('framework/schedule-heartbeat.json');
        file_put_contents($path, json_encode([
            'ran_at' => now()->toIso8601String(),
            'timezone' => (string) config('app.timezone'),
        ], JSON_UNESCAPED_SLASHES));

        return self::SUCCESS;
    }
}
