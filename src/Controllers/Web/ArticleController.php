<?php

namespace App\Controllers\Web;

use App\Controllers\Controller;
use App\Models\Article;
use App\Support\ArticleCovers;
use App\Support\Site;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $page = Site::page('articles');
        $articles = Article::query()
            ->published()
            ->listed()
            ->latest('published_at')
            ->get();

        return view('articles.index', [
            'page' => $page,
            'blocks' => $page?->blockMap() ?? [],
            'articles' => $articles,
        ]);
    }

    public function show(Article $article): View
    {
        abort_unless($article->isPublished(), 404);
        $page = Site::page('articles');

        return view('articles.show', [
            'article' => $article,
            'page' => $page,
            'blocks' => $page?->blockMap() ?? [],
            'more' => Article::query()->published()->listed()->where('id', '!=', $article->id)->latest('published_at')->limit(3)->get(),
        ]);
    }

    public function cover(int $id): BinaryFileResponse
    {
        $article = Article::query()->findOrFail($id);
        abort_unless($article->isPublished(), 404);
        $relative = $article->resolvedCoverPath();
        $absolute = ArticleCovers::absolute($relative);
        $allowed = is_string($relative) && (
            str_starts_with($relative, 'images/articles/')
            || str_starts_with($relative, 'images/article-covers/')
        );
        abort_unless($absolute && $allowed, 404);

        return response()->file($absolute, [
            'Content-Type' => ArticleCovers::mime($relative),
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
