<?php

namespace App\Console\Commands;

use App\Support\MysqlDumper;
use Illuminate\Console\Command;

class ExportAdditiveSqlCommand extends Command
{
    protected $signature = 'db:export-additive
        {--dir= : Output folder}
        {--name=alwin-keep-existing : File name without .sql}';

    protected $description = 'Write a MySQL phpMyAdmin dump that keeps existing rows (no DROP TABLE).';

    public function handle(): int
    {
        $dir = $this->option('dir') ?: base_path();
        if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
            $this->error('Could not create '.$dir);

            return self::FAILURE;
        }

        $name = preg_replace('/[^A-Za-z0-9._-]/', '_', (string) $this->option('name')) ?: 'alwin-keep-existing';
        $path = rtrim($dir, '\\/').DIRECTORY_SEPARATOR.$name.'.sql';

        $bytes = file_put_contents($path, MysqlDumper::additiveDump());
        if ($bytes === false) {
            $this->error('Could not write '.$path);

            return self::FAILURE;
        }

        $this->info('MySQL keep-existing dump: '.$path.' ('.round($bytes / 1024).' KB)');
        $this->line('phpMyAdmin: select the live MySQL database (do not drop it) → Import → this file.');
        $this->warn('Existing rows are kept. Only missing tables, columns, and new rows are added.');

        return self::SUCCESS;
    }
}
