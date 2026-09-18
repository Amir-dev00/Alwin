<?php

namespace App\Providers;

use App\Console\Commands\BackupDatabaseCommand;
use App\Console\Commands\BuildDeployPackage;
use App\Console\Commands\ConvertImagesToWebpCommand;
use App\Console\Commands\ExportAdditiveSqlCommand;
use App\Console\Commands\HeartbeatCommand;
use App\Console\Commands\PublishArticleCoverAliasesCommand;
use App\Models\ContactInquiry;
use App\View\Composers\SiteComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadMigrationsFrom(base_path('migrations'));
        $this->commands([
            BackupDatabaseCommand::class,
            ExportAdditiveSqlCommand::class,
            ConvertImagesToWebpCommand::class,
            PublishArticleCoverAliasesCommand::class,
            HeartbeatCommand::class,
            BuildDeployPackage::class,
        ]);
    }

    public function boot(): void
    {
        Blade::anonymousComponentPath(base_path('templates/layouts'), 'layouts');
        Blade::anonymousComponentPath(base_path('templates/partials'), 'partials');

        Paginator::defaultView('vendor.pagination.admin');

        View::composer('admin.layout', function ($view) {
            $view->with(
                'newInquiryCount',
                ContactInquiry::query()->where('status', ContactInquiry::STATUS_NEW)->count()
            );
        });

        View::composer([
            'layouts.*',
            'partials.*',
            'pages.*',
            'products.*',
            'projects.*',
            'articles.*',
            'errors.*',
        ], SiteComposer::class);

        if ($this->app->environment('production')) {
            DB::prohibitDestructiveCommands();
            if ($root = config('app.url')) {
                URL::forceRootUrl($root);
            }
            URL::forceScheme('https');
        }
    }
}
