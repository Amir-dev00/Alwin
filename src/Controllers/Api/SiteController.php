<?php

namespace App\Controllers\Api;

use App\Controllers\Controller;
use App\Models\Article;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\Partner;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Project;
use App\Models\Setting;
use App\Support\Site;
use App\Support\SiteCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SiteController extends Controller
{
    public function site(): JsonResponse
    {
        $payload = Cache::remember(SiteCache::KEYS[0], 30, function () {
            $pages = Page::query()->with('blocks')->get()->mapWithKeys(function (Page $page) {
                return [$page->key => [
                    'title' => $page->title,
                    'path' => $page->path,
                    'seo' => [
                        'title' => $page->seo_title,
                        'description' => $page->seo_description,
                    ],
                    'blocks' => $page->blockMap(),
                ]];
            });

            return [
                'settings' => Setting::map(),
                'navigation' => [
                    'header' => NavigationItem::query()->where('location', 'header')->where('is_active', true)->orderBy('sort_order')->get()->map(fn (NavigationItem $item) => self::navItem($item))->values(),
                    'footer' => NavigationItem::query()->where('location', 'footer')->where('is_active', true)->orderBy('sort_order')->get()->map(fn (NavigationItem $item) => self::navItem($item))->values(),
                ],
                'partners' => Partner::query()->with('image')->where('is_active', true)->orderBy('sort_order')->get()->map->toPublicArray()->values(),
                'pages' => $pages,
                'generated_at' => now()->toIso8601String(),
            ];
        });

        return response()->json($payload)->header('Cache-Control', 'public, max-age=15');
    }

    public function home(): JsonResponse
    {
        $payload = Cache::remember(SiteCache::KEYS[3], 30, function () {
            return [
                'projects' => Project::query()
                    ->with('image')
                    ->where('status', 'published')
                    ->where('show_on_home', true)
                    ->orderBy('sort_order')
                    ->limit(6)
                    ->get()
                    ->map
                    ->toPublicArray()
                    ->values(),
                'products' => Product::query()
                    ->with(['category', 'imageClose', 'imageOpen'])
                    ->where('status', 'published')
                    ->where('show_on_home', true)
                    ->orderBy('sort_order')
                    ->limit(4)
                    ->get()
                    ->map
                    ->toPublicArray()
                    ->values(),
                'partners' => Partner::query()
                    ->with('image')
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->get()
                    ->map
                    ->toPublicArray()
                    ->values(),
                'project_count' => Project::query()->where('status', 'published')->count(),
                'product_count' => Product::query()->where('status', 'published')->count(),
            ];
        });

        return response()->json($payload)->header('Cache-Control', 'public, max-age=15');
    }

    public function products(): JsonResponse
    {
        $payload = Cache::remember(SiteCache::KEYS[1], 30, function () {
            $products = Product::query()
                ->with(['category', 'imageClose', 'imageOpen'])
                ->where('status', 'published')
                ->listed()
                ->orderBy('sort_order')
                ->get()
                ->map->toPublicArray()
                ->values();

            $categories = ProductCategory::query()
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->pluck('name');

            return [
                'schema_version' => '1.1',
                'summary' => [
                    'product_count' => $products->count(),
                    'categories' => $categories,
                ],
                'products' => $products,
            ];
        });

        return response()->json($payload);
    }

    public function projects(): JsonResponse
    {
        $payload = Cache::remember(SiteCache::KEYS[2], 30, function () {
            $projects = Project::query()
                ->with('image')
                ->where('status', 'published')
                ->listed()
                ->orderBy('sort_order')
                ->get()
                ->map->toPublicArray()
                ->values();

            return [
                'projects' => $projects,
                'project_count' => $projects->count(),
            ];
        });

        return response()->json($payload);
    }

    public function project(string $slug): JsonResponse
    {
        $project = Project::query()
            ->with('image')
            ->where('status', 'published')
            ->where('slug', $slug)
            ->firstOrFail();

        $siblings = Project::query()
            ->where('status', 'published')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'slug', 'title']);

        $index = $siblings->search(fn (Project $item) => $item->id === $project->id);

        $neighbor = function (mixed $item): ?array {
            if (! $item instanceof Project) {
                return null;
            }

            return ['slug' => $item->slug, 'title' => $item->title];
        };

        return response()->json([
            'project' => $project->toPublicArray(),
            'prev' => $index !== false ? $neighbor($siblings->get($index - 1)) : null,
            'next' => $index !== false ? $neighbor($siblings->get($index + 1)) : null,
        ]);
    }

    public function articles(Request $request): JsonResponse
    {
        $articles = Article::query()
            ->published()
            ->listed()
            ->latest('published_at')
            ->get();

        return response()->json([
            'articles' => $articles->map(fn (Article $article) => $article->toPublicArray())->values(),
            'meta' => [
                'current_page' => 1,
                'last_page' => 1,
                'total' => $articles->count(),
            ],
        ]);
    }

    public function article(string $slug): JsonResponse
    {
        $article = Article::query()->published()->where('slug', $slug)->firstOrFail();
        $more = Article::query()
            ->published()
            ->listed()
            ->where('id', '!=', $article->id)
            ->latest('published_at')
            ->limit(3)
            ->get()
            ->map
            ->toPublicArray()
            ->values();

        return response()->json([
            'article' => $article->toPublicArray(true),
            'more' => $more,
        ]);
    }

    private static function navItem(NavigationItem $item): array
    {
        $href = Site::href($item->url);
        $path = parse_url($href, PHP_URL_PATH) ?: '/';

        return [
            'label' => $item->label,
            'url' => $path,
            'path' => $path,
            'sort_order' => $item->sort_order,
        ];
    }
}
