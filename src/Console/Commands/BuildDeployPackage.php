<?php

namespace App\Console\Commands;

use App\Support\BuildInfo;
use App\Support\MysqlDumper;
use App\Support\UnixZipPerms;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use ZipArchive;

class BuildDeployPackage extends Command
{
    protected $signature = 'deploy:package
        {--dir= : Output folder for the zip and SQL dump}
        {--sql-only : Only write db.sql (skip the zip)}';

    protected $description = 'Build a single cPanel zip of the ALWIN application plus a MySQL dump';

    public function handle(): int
    {
        $outDir = $this->option('dir') ?: base_path('scripts');
        if (! is_dir($outDir) && ! mkdir($outDir, 0755, true) && ! is_dir($outDir)) {
            $this->error('Could not create '.$outDir);

            return self::FAILURE;
        }

        $this->info('Writing sanitized MySQL dump…');
        $sql = MysqlDumper::dump();
        $sqlPath = $outDir.DIRECTORY_SEPARATOR.'alwin-production.sql';
        file_put_contents($sqlPath, $sql);
        $this->line('  '.$sqlPath.' ('.self::kb(filesize($sqlPath)).')');

        if ($this->option('sql-only')) {
            return self::SUCCESS;
        }

        $zipPath = $outDir.DIRECTORY_SEPARATOR.'alwin-cpanel.zip';
        $this->info('Zipping application…');
        $zipPath = $this->zipPackage($zipPath);
        $this->line('  '.$zipPath.' ('.self::kb(filesize($zipPath)).')');
        $this->info('First install: upload the zip into public_html, import the SQL into an EMPTY database, then edit .env.');
        $this->warn('Existing production: keep the live database. Upload code only, then run migrate. Never re-import this SQL over live data.');

        return self::SUCCESS;
    }

    private function zipPackage(string $zipPath): string
    {
        $tempZip = sys_get_temp_dir().DIRECTORY_SEPARATOR.'alwin-deploy-'.uniqid('', true).'.zip';
        $zip = new ZipArchive;
        if ($zip->open($tempZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Could not create zip at '.$tempZip);
        }

        $count = 0;
        $skipped = 0;
        $pending = 0;
        $flush = function () use (&$zip, $tempZip, &$pending): void {
            if ($pending === 0) {
                return;
            }
            $zip->close();
            $zip = new ZipArchive;
            if ($zip->open($tempZip) !== true) {
                throw new \RuntimeException('Could not reopen zip at '.$tempZip);
            }
            $pending = 0;
        };

        $add = function (string $abs, string $local) use (&$zip, &$count, &$skipped, &$pending, $flush): void {
            $local = str_replace('\\', '/', $local);
            if (is_dir($abs)) {
                $name = rtrim($local, '/');
                $zip->addEmptyDir($name);
                UnixZipPerms::apply($zip, $name, true);
                $pending++;

                return;
            }
            if ($this->shouldSkipFile($abs) || ! $this->canRead($abs)) {
                $skipped++;

                return;
            }
            if (! $zip->addFile($abs, $local)) {
                $skipped++;

                return;
            }
            UnixZipPerms::apply($zip, $local, false, UnixZipPerms::isExecutable($local));
            $count++;
            $pending++;
            if ($pending >= 250) {
                $flush();
            }
        };

        foreach (['api', 'assets', 'bootstrap', 'config', 'data', 'database', 'images', 'includes', 'migrations', 'public', 'scripts', 'src', 'templates', 'vendor'] as $dir) {
            $from = base_path($dir);
            if (is_dir($from)) {
                $this->addTree($from, $dir, $add);
            }
        }
        foreach (['artisan', 'composer.json', 'composer.lock', 'index.php', '.htaccess', '.env.example', 'robots.txt', 'README.md'] as $file) {
            $abs = base_path($file);
            if (is_file($abs)) {
                $add($abs, $file);
            }
        }

        $flush();
        $this->addStoragePlaceholders($zip);
        $env = $this->productionEnv();
        $this->addString($zip, '.env.example', $env);

        $this->newLine();
        $this->line("  {$count} files".($skipped ? ", skipped {$skipped}" : ''));
        $zip->close();

        $zip = new ZipArchive;
        if ($zip->open($tempZip) !== true) {
            throw new \RuntimeException('Could not reopen zip to normalize permissions at '.$tempZip);
        }
        UnixZipPerms::normalizeArchive($zip);
        $zip->close();

        if (is_file($zipPath) && ! @unlink($zipPath)) {
            $zipPath = dirname($zipPath).DIRECTORY_SEPARATOR.'alwin-cpanel-'.date('Ymd-His').'.zip';
        }
        if (! @rename($tempZip, $zipPath) && ! @copy($tempZip, $zipPath)) {
            throw new \RuntimeException('Could not move zip to '.$zipPath);
        }
        @unlink($tempZip);

        return $zipPath;
    }

    private function shouldSkipFile(string $abs): bool
    {
        $base = strtolower(basename($abs));
        if (str_starts_with($base, '.env') && $base !== '.env.example') {
            return true;
        }
        if (str_contains($base, '.sqlite') || str_ends_with($base, '.log') || $base === '.ds_store' || $base === 'thumbs.db') {
            return true;
        }

        $normalized = str_replace('\\', '/', strtolower($abs));
        foreach (['/node_modules/', '/_obsolete/', '/cms/', '/storage/framework/', '/storage/logs/'] as $skip) {
            if (str_contains($normalized, $skip)) {
                return true;
            }
        }

        return in_array(pathinfo($abs, PATHINFO_EXTENSION), ['scss', 'map'], true);
    }

    private function canRead(string $abs): bool
    {
        $handle = @fopen($abs, 'rb');
        if ($handle === false) {
            return false;
        }
        fclose($handle);

        return true;
    }

    private function addTree(string $from, string $prefix, callable $add): void
    {
        if (! is_dir($from)) {
            return;
        }
        $from = realpath($from) ?: $from;
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($from, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($iterator as $file) {
            $abs = $file->getPathname();
            $rel = substr($abs, strlen($from) + 1);
            $add($abs, $prefix.'/'.str_replace('\\', '/', $rel));
        }
    }

    private function addString(ZipArchive $zip, string $name, string $contents, bool $executable = false): void
    {
        $zip->addFromString($name, $contents);
        UnixZipPerms::apply($zip, $name, false, $executable);
    }

    private function addStoragePlaceholders(ZipArchive $zip): void
    {
        $keep = "*\n!.gitignore\n";
        foreach ([
            'storage/app/public',
            'storage/app/private',
            'storage/framework/cache/data',
            'storage/framework/sessions',
            'storage/framework/views',
            'storage/logs',
        ] as $dir) {
            $zip->addEmptyDir($dir);
            UnixZipPerms::apply($zip, $dir, true);
            $this->addString($zip, $dir.'/.gitignore', $keep);
        }
    }

    private function productionEnv(): string
    {
        $key = config('app.key') ?: 'base64:'.base64_encode(Str::random(32));
        $build = BuildInfo::snapshot();
        $version = $build['version'];
        $date = $build['build_date'] ?? now()->toDateTimeString();
        $commit = $build['git_commit'] ?? '';
        $cron = config('alwin.cron_secret') ?: Str::random(40);

        return <<<ENV
APP_NAME=ALWIN
APP_ENV=production
APP_KEY={$key}
APP_DEBUG=false
APP_URL=https://alwinco.ir
API_URL=/api/v1
APP_VERSION={$version}
APP_BUILD_DATE={$date}
APP_GIT_COMMIT={$commit}

APP_LOCALE=fa
APP_FALLBACK_LOCALE=fa
APP_FAKER_LOCALE=fa_IR
APP_TIMEZONE=Asia/Tehran

APP_MAINTENANCE_DRIVER=file
BCRYPT_ROUNDS=12
LOG_CHANNEL=stack
LOG_STACK=single
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=true

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=public
QUEUE_CONNECTION=sync
CACHE_STORE=file

MAIL_MAILER=log
MAIL_FROM_ADDRESS="info@alwinco.ir"
MAIL_FROM_NAME="ALWIN"

ADMIN_EMAIL=admin@alwinco.ir
CRON_SECRET={$cron}
ENV;
    }

    private static function kb(int $bytes): string
    {
        if ($bytes > 1024 * 1024) {
            return round($bytes / 1024 / 1024, 1).' MB';
        }

        return round($bytes / 1024).' KB';
    }
}
