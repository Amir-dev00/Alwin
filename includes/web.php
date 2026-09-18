<?php

use App\Controllers\Admin\ActivityLogController;
use App\Controllers\Admin\ArticleController as AdminArticleController;
use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\ContactInquiryController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\MediaController;
use App\Controllers\Admin\NavigationController;
use App\Controllers\Admin\PageController;
use App\Controllers\Admin\PartnerController;
use App\Controllers\Admin\PricingController;
use App\Controllers\Admin\ProductCategoryController;
use App\Controllers\Admin\ProductController;
use App\Controllers\Admin\ProjectController;
use App\Controllers\Admin\SettingController;
use App\Controllers\Admin\SystemCheckController;
use App\Controllers\Admin\UserController;
use App\Controllers\CronController;
use App\Controllers\Web\AboutController;
use App\Controllers\Web\ArticleController;
use App\Controllers\Web\ContactController;
use App\Controllers\Web\HomeController;
use App\Controllers\Web\ProductCatalogController;
use App\Controllers\Web\ProjectCatalogController;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/cron/{token}', CronController::class)->name('cron');

if (filter_var(env('ADMIN_ONLY', false), FILTER_VALIDATE_BOOLEAN)) {
    Route::redirect('/', '/admin');
}

Route::get('/', HomeController::class)->name('home');
Route::get('/about', AboutController::class)->name('about');
Route::get('/products', ProductCatalogController::class)->name('products');
Route::get('/projects', [ProjectCatalogController::class, 'index'])->name('projects.index');
Route::get('/projects/{project:slug}', [ProjectCatalogController::class, 'show'])->name('projects.show');
Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
Route::get('/article-covers/{id}', [ArticleController::class, 'cover'])->whereNumber('id')->name('articles.cover');
Route::get('/articles/{article:slug}', [ArticleController::class, 'show'])->name('articles.show');
Route::get('/contact', ContactController::class)->name('contact');

Route::permanentRedirect('/index.html', '/');
Route::permanentRedirect('/about.html', '/about');
Route::permanentRedirect('/services.html', '/products');
Route::permanentRedirect('/contact.html', '/contact');
Route::permanentRedirect('/portfolio.html', '/projects');
Route::permanentRedirect('/articles/index.html', '/articles');
Route::get('/project.html', function (Request $request) {
    $slug = $request->query('slug');
    if ($slug) {
        return redirect()->route('projects.show', $slug, 301);
    }

    return redirect()->route('projects.index', 301);
});
Route::get('/articles/{slug}/index.html', fn (string $slug) => redirect()->route('articles.show', $slug, 301));

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'show'])->name('login');
        Route::post('login', [AuthController::class, 'login'])
            ->middleware('throttle:5,1')
            ->name('login.store');
    });

    Route::middleware(['auth', 'admin.active'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('inquiries', [ContactInquiryController::class, 'index'])->name('inquiries.index');
        Route::post('inquiries/{inquiry}/toggle', [ContactInquiryController::class, 'toggle'])->name('inquiries.toggle');

        Route::resource('products', ProductController::class)->except(['show']);
        Route::post('products/{product}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate');
        Route::post('products/{product}/listing', [ProductController::class, 'listing'])->name('products.listing');
        Route::post('products/{id}/restore', [ProductController::class, 'restore'])->name('products.restore');

        Route::resource('categories', ProductCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::resource('projects', ProjectController::class)->except(['show']);
        Route::post('projects/{project}/duplicate', [ProjectController::class, 'duplicate'])->name('projects.duplicate');
        Route::post('projects/{project}/listing', [ProjectController::class, 'listing'])->name('projects.listing');
        Route::post('projects/{id}/restore', [ProjectController::class, 'restore'])->name('projects.restore');

        Route::resource('articles', AdminArticleController::class)->except(['show']);
        Route::post('articles/{article}/listing', [AdminArticleController::class, 'listing'])->name('articles.listing');
        Route::post('articles/{id}/restore', [AdminArticleController::class, 'restore'])->name('articles.restore');

        Route::get('pages', [PageController::class, 'index'])->name('pages.index');
        Route::get('homepage', [PageController::class, 'home'])->name('homepage');
        Route::get('pages/{page}/edit', [PageController::class, 'edit'])->name('pages.edit');
        Route::put('pages/{page}', [PageController::class, 'update'])->name('pages.update');

        Route::get('media', [MediaController::class, 'index'])->name('media.index');
        Route::post('media', [MediaController::class, 'store'])->name('media.store');
        Route::put('media/{medium}', [MediaController::class, 'update'])->name('media.update');
        Route::delete('media/{medium}', [MediaController::class, 'destroy'])->name('media.destroy');

        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

        Route::get('navigation', [NavigationController::class, 'index'])->name('navigation.index');
        Route::post('navigation', [NavigationController::class, 'store'])->name('navigation.store');
        Route::put('navigation/{navigation}', [NavigationController::class, 'update'])->name('navigation.update');
        Route::delete('navigation/{navigation}', [NavigationController::class, 'destroy'])->name('navigation.destroy');
        Route::post('navigation/reorder', [NavigationController::class, 'reorder'])->name('navigation.reorder');

        Route::resource('partners', PartnerController::class)->except(['show', 'create', 'edit']);

        Route::get('pricing', [PricingController::class, 'index'])->name('pricing.index');
        Route::post('pricing/preview', [PricingController::class, 'preview'])->name('pricing.preview');
        Route::put('pricing/brands', [PricingController::class, 'updateBrands'])->name('pricing.brands.update');
        Route::put('pricing/glasses', [PricingController::class, 'updateGlasses'])->name('pricing.glasses.update');
        Route::post('pricing/glasses', [PricingController::class, 'storeGlass'])->name('pricing.glasses.store');
        Route::put('pricing/hardwares', [PricingController::class, 'updateHardwares'])->name('pricing.hardwares.update');
        Route::post('pricing/hardwares', [PricingController::class, 'storeHardware'])->name('pricing.hardwares.store');
        Route::put('pricing/models', [PricingController::class, 'updateModels'])->name('pricing.models.update');
        Route::post('pricing/models/{model}/rebuild', [PricingController::class, 'rebuildRecipe'])->name('pricing.models.rebuild');
        Route::put('pricing/settings', [PricingController::class, 'updateSettings'])->name('pricing.settings.update');
        Route::post('pricing/leads/{lead}/toggle', [PricingController::class, 'markLead'])->name('pricing.leads.toggle');

        Route::get('activity', [ActivityLogController::class, 'index'])->name('activity.index');

        Route::middleware('admin.super')->group(function () {
            Route::get('system-check', [SystemCheckController::class, 'show'])->name('system.check');
            Route::post('system-check/migrate', [SystemCheckController::class, 'migrate'])->name('system.migrate');
            Route::get('users', [UserController::class, 'index'])->name('users.index');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
            Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        });
    });
});

Route::get('/{legacy}', function (string $legacy) {
    $article = Article::query()->where('slug', $legacy)->first();
    abort_unless($article, 404);

    return redirect()->route('articles.show', $article, 301);
})->where('legacy', '^(?!admin$|admin/|api$|api/|up$|cron$|cron/|storage/|assets/|images/|public/).+');
