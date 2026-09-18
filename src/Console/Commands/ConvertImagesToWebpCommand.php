<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Media;
use App\Models\Setting;
use App\Services\ImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ConvertImagesToWebpCommand extends Command
{
    protected $signature = 'images:convert-webp';

    protected $description = 'Convert existing uploaded images to optimized WebP without deleting catalog site files';

    public function handle(ImageService $images): int
    {
        if (! function_exists('imagewebp')) {
            $this->error('PHP GD with WebP support is required.');

            return self::FAILURE;
        }

        $converted = 0;
        $skipped = 0;
        $failed = 0;

        $this->info('Converting media library uploads...');
        Media::query()->where('kind', 'image')->where('disk', 'public')->orderBy('id')->each(function (Media $media) use ($images, &$converted, &$skipped, &$failed) {
            if ($this->alreadyWebp($media->path) && Storage::disk('public')->exists($media->path)) {
                $skipped++;

                return;
            }
            if (! Storage::disk('public')->exists($media->path) && ! is_file(base_path(str_replace('/', DIRECTORY_SEPARATOR, $media->path)))) {
                $skipped++;

                return;
            }

            $oldPath = $media->path;
            try {
                $stored = $images->convert($oldPath, $media->guessCollection(), $media->original_name ?: $media->filename);
                $media->fill([
                    'disk' => $stored['disk'],
                    'path' => $stored['path'],
                    'filename' => $stored['filename'],
                    'mime' => $stored['mime'],
                    'size' => $stored['size'],
                    'width' => $stored['width'],
                    'height' => $stored['height'],
                ]);
                $media->save();
                if ($oldPath !== $stored['path']) {
                    $images->delete($oldPath);
                }
                $converted++;
                $this->line('  media #'.$media->id.' → '.$stored['path']);
            } catch (Throwable $e) {
                $failed++;
                $this->warn('  media #'.$media->id.': '.$e->getMessage());
            }
        });

        $this->info('Converting article covers...');
        Article::withTrashed()->whereNotNull('cover_path')->orderBy('id')->each(function (Article $article) use ($images, &$converted, &$skipped, &$failed) {
            $path = ltrim(str_replace('\\', '/', (string) $article->cover_path), '/');
            if ($path === '' || str_contains($path, '..') || ! str_starts_with($path, 'articles/')) {
                $skipped++;

                return;
            }
            try {
                if ($this->alreadyWebp($path) || ! Storage::disk('public')->exists($path)) {
                    $skipped++;

                    return;
                }
            } catch (Throwable) {
                $skipped++;

                return;
            }
            try {
                $stored = $images->convert($path, 'articles', basename($path));
                $old = $article->cover_path;
                $article->cover_path = $stored['path'];
                $article->save();
                if ($old !== $stored['path']) {
                    $images->delete($old);
                }
                $converted++;
                $this->line('  article #'.$article->id.' → '.$stored['path']);
            } catch (Throwable $e) {
                $failed++;
                $this->warn('  article #'.$article->id.': '.$e->getMessage());
            }
        });

        $this->info('Converting setting images...');
        Setting::query()->whereIn('type', ['image', 'file'])->orderBy('id')->each(function (Setting $setting) use ($images, &$converted, &$skipped, &$failed) {
            $path = ltrim(str_replace('\\', '/', (string) $setting->value), '/');
            if ($path === '' || str_contains($path, '..') || ! str_starts_with($path, 'settings/')) {
                $skipped++;

                return;
            }
            try {
                if ($this->alreadyWebp($path) || ! Storage::disk('public')->exists($path)) {
                    $skipped++;

                    return;
                }
            } catch (Throwable) {
                $skipped++;

                return;
            }
            try {
                $stored = $images->convert($path, 'settings', basename($path));
                $old = $setting->value;
                $setting->value = $stored['path'];
                $setting->save();
                if ($old !== $stored['path']) {
                    $images->delete($old);
                }
                $converted++;
                $this->line('  setting '.$setting->key.' → '.$stored['path']);
            } catch (Throwable $e) {
                $failed++;
                $this->warn('  setting '.$setting->key.': '.$e->getMessage());
            }
        });

        $this->newLine();
        $this->info("Done. Converted {$converted}, skipped {$skipped}, failed {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function alreadyWebp(string $path): bool
    {
        return str_ends_with(strtolower($path), '.webp');
    }
}
