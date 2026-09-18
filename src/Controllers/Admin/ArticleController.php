<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Exceptions\ImageException;
use App\Models\Article;
use App\Services\ImageService;
use App\Support\Audit;
use App\Support\AutoFill;
use App\Support\SiteCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function __construct(private ImageService $images)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Article::class);
        $q = Article::query();
        if ($request->filled('search')) {
            $s = $request->string('search')->toString();
            $q->where(function ($qq) use ($s) {
                $qq->where('title', 'like', "%{$s}%")->orWhere('slug', 'like', "%{$s}%");
            });
        }
        if ($request->filled('status')) {
            $q->where('status', $request->string('status'));
        }
        if ($request->boolean('trashed')) {
            $q->onlyTrashed();
        }
        if ($request->filled('listing')) {
            $q->where('show_on_listing', $request->string('listing')->toString() === '1');
        }

        return view('admin.articles.index', [
            'items' => $q->latest('published_at')->paginate(12)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Article::class);

        return view('admin.articles.form', [
            'item' => new Article(['status' => 'published', 'show_on_listing' => true, 'author' => 'آلوین', 'published_at' => now()]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Article::class);
        try {
            $data = $this->validated($request);
        } catch (ImageException $e) {
            return back()->withInput()->withErrors(['cover' => $e->getMessage()]);
        }
        $item = Article::query()->create($data);
        Audit::log('create', $item, 'ایجاد مقاله '.$item->title);
        SiteCache::flush();

        return redirect()->route('admin.articles.edit', $item)->with('success', 'مقاله ذخیره شد.');
    }

    public function edit(Article $article): View
    {
        $this->authorize('update', $article);

        return view('admin.articles.form', ['item' => $article]);
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $this->authorize('update', $article);
        $oldCover = $article->cover_path;
        try {
            $data = $this->validated($request, $article);
        } catch (ImageException $e) {
            return back()->withInput()->withErrors(['cover' => $e->getMessage()]);
        }
        $article->update($data);
        if (($data['cover_path'] ?? null) && $data['cover_path'] !== $oldCover) {
            $this->images->delete($oldCover);
        }
        Audit::log('update', $article, 'ویرایش مقاله '.$article->title);
        SiteCache::flush();

        return back()->with('success', 'تغییرات مقاله ذخیره شد.');
    }

    public function destroy(Article $article): RedirectResponse
    {
        $this->authorize('delete', $article);
        $title = $article->title;
        $article->delete();
        Audit::log('delete', $article, 'حذف مقاله '.$title);
        SiteCache::flush();

        return redirect()->route('admin.articles.index')->with('success', 'مقاله به سطل زباله منتقل شد.');
    }

    public function restore(int $id): RedirectResponse
    {
        $item = Article::onlyTrashed()->findOrFail($id);
        $this->authorize('update', $item);
        $item->restore();
        Audit::log('restore', $item, 'بازیابی مقاله '.$item->title);
        SiteCache::flush();

        return back()->with('success', 'مقاله بازیابی شد.');
    }

    public function listing(Request $request, Article $article): RedirectResponse
    {
        $this->authorize('update', $article);
        $article->update(['show_on_listing' => $request->boolean('show_on_listing')]);
        Audit::log('update', $article, 'نمایش فهرست مقاله '.$article->title);
        SiteCache::flush();

        return back()->with('success', $article->show_on_listing ? 'مقاله در صفحه مقالات نمایش داده می‌شود.' : 'مقاله از صفحه مقالات برداشته شد.');
    }

    private function validated(Request $request, ?Article $article = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', Rule::unique('articles', 'slug')->ignore($article?->id)],
            'excerpt' => ['nullable', 'string'],
            'body' => ['nullable', 'string'],
            'cover' => ['nullable', 'file', 'max:'.(int) config('image.max_kilobytes', 51200)],
            'cover_alt' => ['nullable', 'string', 'max:190'],
            'author' => ['nullable', 'string', 'max:120'],
            'published_at' => ['nullable', 'date'],
            'status' => ['required', 'in:draft,published'],
            'show_on_listing' => ['sometimes', 'boolean'],
            'seo_title' => ['nullable', 'string', 'max:190'],
            'seo_description' => ['nullable', 'string', 'max:320'],
        ], ['title.required' => 'عنوان مقاله الزامی است.']);

        unset($data['cover']);
        $data['cover_path'] = $article?->cover_path;

        if ($request->hasFile('cover')) {
            $stored = $this->images->store($request->file('cover'), 'articles');
            $data['cover_path'] = $stored['path'];
        }

        if (empty($data['slug'])) {
            $data['slug'] = AutoFill::slug($data['title'], 'article');
        }
        if (empty($data['seo_title'])) {
            $data['seo_title'] = AutoFill::seoTitle($data['title']);
        }
        if (empty($data['seo_description'])) {
            $data['seo_description'] = AutoFill::seoDescription($data['excerpt'] ?? null, $data['title']);
        }
        if (empty($data['cover_alt'])) {
            $data['cover_alt'] = $data['title'];
        }
        $data['show_on_listing'] = $request->boolean('show_on_listing');

        return $data;
    }
}
