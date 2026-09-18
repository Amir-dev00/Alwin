<?php

namespace App\Console\Commands;

use App\Support\MysqlDumper;
use Illuminate\Console\Command;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'db:backup
        {--name= : File name without .sql}
        {--dir= : Folder to write the file into}';

    protected $description = 'Write a MySQL/SQLite dump for restore. Does not change the live database.';

    public function handle(): int
    {
        $dir = $this->option('dir') ?: storage_path('backups');
        if (! is_dir($dir) && ! mkdir($dir, 0755, true) && ! is_dir($dir)) {
            $this->error('Could not create '.$dir);

            return self::FAILURE;
        }

        $name = $this->option('name') ?: 'alwin_backup_'.now()->format('Ymd_His');
        $name = preg_replace('/[^A-Za-z0-9._-]/', '_', (string) $name) ?: 'alwin_backup';
        $path = rtrim($dir, '\\/').DIRECTORY_SEPARATOR.$name.'.sql';

        $bytes = file_put_contents($path, MysqlDumper::dump());
        if ($bytes === false) {
            $this->error('Could not write '.$path);

            return self::FAILURE;
        }

        $this->info('Backup written: '.$path.' ('.round($bytes / 1024).' KB)');
        $this->warn('This file is for restore or archive only. Do not import it into a live database that already has data.');

        return self::SUCCESS;
    }
}
