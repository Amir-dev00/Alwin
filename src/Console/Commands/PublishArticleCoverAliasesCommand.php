<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Support\ArticleCovers;
use App\Support\ArticleImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishArticleCoverAliasesCommand extends Command
{
    protected $signature = 'articles:alias-covers';

    protected $description = 'Copy article covers to ASCII filenames that cPanel/zip can upload.';

    public function handle(): int
    {
        $outDir = base_path('images'.DIRECTORY_SEPARATOR.'article-covers');
        if (! is_dir($outDir) && ! mkdir($outDir, 0755, true) && ! is_dir($outDir)) {
            $this->error('Could not create '.$outDir);

            return self::FAILURE;
        }

        $copied = 0;
        $slugs = [];
        foreach (ArticleCovers::mapping() as $slug => $relative) {
            $slugs[$slug] = $relative;
        }
        $root = ArticleImporter::sourceRoot();
        if (is_dir($root)) {
            foreach (File::directories($root) as $dir) {
                $slug = basename($dir);
                if (isset($slugs[$slug]) || in_array($slug, ['page', 'pages'], true) || str_starts_with($slug, '.')) {
                    continue;
                }
                $article = Article::query()->where('slug', $slug)->first();
                $resolved = $article ? ArticleCovers::resolve($article) : null;
                if ($resolved) {
                    $slugs[$slug] = $resolved;
                }
            }
        }
        foreach (Article::query()->orderBy('id')->get() as $article) {
            if (! isset($slugs[$article->slug])) {
                $resolved = ArticleCovers::resolve($article);
                if ($resolved) {
                    $slugs[$article->slug] = $resolved;
                }
            }
        }

        foreach ($slugs as $slug => $relative) {
            $source = ArticleCovers::absolute($relative);
            if (! $source) {
                $this->warn('Missing source for '.$slug);

                continue;
            }
            $ext = strtolower(pathinfo($source, PATHINFO_EXTENSION)) ?: 'webp';
            $destRel = ArticleCovers::aliasRelative($slug, $ext);
            $dest = base_path(str_replace('/', DIRECTORY_SEPARATOR, $destRel));
            if (! @copy($source, $dest)) {
                $this->warn('Could not copy '.$source);

                continue;
            }
            $copied++;
        }

        $this->info("Copied {$copied} ASCII article covers into images/article-covers.");

        return self::SUCCESS;
    }
}
