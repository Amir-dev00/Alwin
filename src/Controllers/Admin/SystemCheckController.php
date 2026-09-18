<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Support\BuildInfo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Throwable;

class SystemCheckController extends Controller
{
    public function show(): View
    {
        return view('admin.system-check', [
            'checks' => $this->checks(),
            'build' => BuildInfo::snapshot(),
            'logs' => $this->recentLogs(),
            'cronHint' => $this->cronHint(),
        ]);
    }

    public function migrate(Request $request): RedirectResponse
    {
        $lock = fopen(storage_path('framework/migrate.lock'), 'c+');
        if ($lock === false || ! flock($lock, LOCK_EX | LOCK_NB)) {
            if (is_resource($lock)) {
                fclose($lock);
            }

            return back()->withErrors(['migrate' => 'یک به‌روزرسانی پایگاه‌داده همین حالا در حال اجراست.']);
        }

        try {
            Artisan::call('migrate', ['--force' => true]);
            $output = trim(Artisan::output()) ?: 'هیچ مهاجرت معلقی نبود.';

            return back()->with('success', $output);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['migrate' => 'مهاجرت شکست خورد. جزئیات در لاگ سیستم است.']);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function checks(): array
    {
        $required = ['users', 'settings', 'products', 'projects', 'articles', 'pages'];
        $missing = [];
        try {
            DB::connection()->getPdo();
            $db = ['ok' => true, 'detail' => DB::connection()->getDriverName()];
            foreach ($required as $table) {
                if (! Schema::hasTable($table)) {
                    $missing[] = $table;
                }
            }
        } catch (Throwable $e) {
            $db = ['ok' => false, 'detail' => 'اتصال برقرار نشد'];
            $missing = $required;
        }

        $storageOk = is_writable(storage_path())
            && is_writable(storage_path('logs'))
            && is_writable(storage_path('framework'))
            && is_writable(base_path('bootstrap/cache'));

        $extensions = ['pdo', 'mbstring', 'openssl', 'tokenizer', 'json', 'ctype', 'fileinfo', 'gd'];
        $missingExt = array_values(array_filter($extensions, fn (string $ext) => ! extension_loaded($ext)));
        if (config('database.default') === 'mysql' && ! extension_loaded('pdo_mysql')) {
            $missingExt[] = 'pdo_mysql';
        }

        $heartbeat = $this->heartbeat();
        $apiOk = collect(\Illuminate\Support\Facades\Route::getRoutes())->contains(
            fn ($route) => str_contains($route->uri(), 'api/v1/products')
        );

        return [
            ['label' => 'اتصال پایگاه‌داده', 'ok' => $db['ok'], 'detail' => $db['detail']],
            ['label' => 'جدول‌های لازم', 'ok' => $missing === [], 'detail' => $missing === [] ? 'کامل' : implode(', ', $missing)],
            ['label' => 'پوشه ذخیره‌سازی قابل نوشتن', 'ok' => $storageOk, 'detail' => $storageOk ? 'storage / bootstrap/cache' : 'دسترسی نوشتن ناکافی است'],
            ['label' => 'نسخه PHP', 'ok' => PHP_VERSION_ID >= 80200, 'detail' => PHP_VERSION.' (حداقل ۸.۲)'],
            ['label' => 'افزونه‌های PHP', 'ok' => $missingExt === [], 'detail' => $missingExt === [] ? implode(', ', $extensions) : 'کم: '.implode(', ', $missingExt)],
            ['label' => 'تبدیل تصویر WebP', 'ok' => function_exists('imagewebp'), 'detail' => function_exists('imagewebp') ? 'GD imagewebp' : 'افزونه GD با پشتیبانی WebP لازم است'],
            ['label' => 'آدرس سایت', 'ok' => filled(config('app.url')), 'detail' => (string) config('app.url')],
            ['label' => 'منطقه‌زمانی', 'ok' => (string) config('app.timezone') === (string) config('alwin.timezone'), 'detail' => (string) config('app.timezone')],
            ['label' => 'زمان‌بند / کرون', 'ok' => $heartbeat['ok'], 'detail' => $heartbeat['detail']],
            ['label' => 'دسترسی API', 'ok' => $apiOk, 'detail' => '/api/v1/products'],
        ];
    }

    private function heartbeat(): array
    {
        $path = storage_path('framework/schedule-heartbeat.json');
        if (! is_file($path)) {
            return ['ok' => false, 'detail' => 'هنوز ضربانی ثبت نشده — کرون را تنظیم کنید'];
        }
        $data = json_decode((string) file_get_contents($path), true);
        $ran = $data['ran_at'] ?? null;
        if (! $ran) {
            return ['ok' => false, 'detail' => 'فایل ضربان نامعتبر است'];
        }
        $at = \Carbon\Carbon::parse($ran);
        $fresh = $at->greaterThan(now()->subMinutes(10));

        return [
            'ok' => $fresh,
            'detail' => 'آخرین اجرا: '.$at->timezone(config('app.timezone'))->format('Y-m-d H:i:s'),
        ];
    }

    private function recentLogs(): array
    {
        $file = storage_path('logs/laravel.log');
        if (! is_file($file) || ! is_readable($file)) {
            return [];
        }
        $lines = @file($file, FILE_IGNORE_NEW_LINES);
        if (! is_array($lines) || $lines === []) {
            return [];
        }

        return array_slice($lines, -40);
    }

    private function cronHint(): string
    {
        $php = PHP_BINARY ?: 'php';
        $base = base_path();

        return $php.' '.$base.DIRECTORY_SEPARATOR.'artisan schedule:run';
    }
}
