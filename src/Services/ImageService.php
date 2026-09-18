<?php

namespace App\Services;

use App\Exceptions\ImageException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ImageService
{
    /**
     * Validate, convert, resize, and store an uploaded image as optimized WebP.
     *
     * @return array{disk: string, path: string, thumbnail_path: ?string, filename: string, mime: string, size: int, width: int, height: int}
     */
    public function store(UploadedFile $file, string $collection = 'media', array $options = []): array
    {
        $this->assertUpload($file);
        $source = $file->getRealPath();
        if (! is_string($source) || $source === '' || ! is_file($source)) {
            throw ImageException::invalid();
        }

        return $this->processAndStore(
            $source,
            $collection,
            (string) $file->getClientOriginalName(),
            $options
        );
    }

    /**
     * Store a new image, then delete the previous one after success.
     *
     * @return array{disk: string, path: string, thumbnail_path: ?string, filename: string, mime: string, size: int, width: int, height: int}
     */
    public function replace(UploadedFile $file, string $collection, ?string $oldPath = null, array $options = []): array
    {
        $stored = $this->store($file, $collection, $options);
        $this->delete($oldPath);

        return $stored;
    }

    /**
     * Convert an existing file on the public disk (or an absolute path) to WebP.
     *
     * @return array{disk: string, path: string, thumbnail_path: ?string, filename: string, mime: string, size: int, width: int, height: int}
     */
    public function convert(string $path, string $collection = 'media', ?string $originalName = null, array $options = []): array
    {
        [$absolute, $temp] = $this->resolveReadablePath($path);
        $name = $originalName ?: basename($path);

        try {
            return $this->processAndStore($absolute, $collection, $name, $options);
        } finally {
            if ($temp && is_file($absolute)) {
                @unlink($absolute);
            }
        }
    }

    public function delete(?string $path): void
    {
        $relative = $this->normalizeRelativePath((string) $path);
        if ($relative === '' || $this->isProtectedSitePath($relative)) {
            return;
        }

        $disk = Storage::disk($this->disk());
        $disk->delete($relative);
        $thumb = $this->thumbnailPathFor($relative);
        if ($thumb !== $relative) {
            $disk->delete($thumb);
        }
    }

    public function url(string $path): string
    {
        $relative = $this->normalizeRelativePath($path);
        if ($relative === '') {
            return '';
        }

        return url('/storage/'.$relative);
    }

    public function thumbnailUrl(string $path): string
    {
        $relative = $this->normalizeRelativePath($path);
        if ($relative === '') {
            return '';
        }

        $thumb = $this->thumbnailPathFor($relative);
        $disk = Storage::disk($this->disk());
        if ($disk->exists($thumb)) {
            return $this->url($thumb);
        }

        return $this->url($relative);
    }

    public function thumbnailPathFor(string $path): string
    {
        $relative = $this->normalizeRelativePath($path);
        if ($relative === '' || str_contains($relative, '/thumbs/')) {
            return $relative;
        }

        $dir = trim(str_replace('\\', '/', dirname($relative)), '.');
        $file = basename($relative);
        if ($dir === '' || $dir === '/') {
            return 'thumbs/'.$file;
        }

        return $dir.'/thumbs/'.$file;
    }

    public function collections(): array
    {
        return array_keys(config('image.collections', []));
    }

    public function normalizeCollection(?string $collection): string
    {
        $collection = strtolower(trim((string) $collection));
        $allowed = $this->collections();
        if ($collection === '' || ! in_array($collection, $allowed, true)) {
            return 'media';
        }

        return $collection;
    }

    public function isImageUpload(UploadedFile $file): bool
    {
        $mime = $this->detectMime($file->getRealPath() ?: '');
        $ext = strtolower($file->getClientOriginalExtension());

        return in_array($mime, $this->allowedMimes(), true)
            || in_array($ext, $this->allowedExtensions(), true);
    }

    /**
     * @return array{disk: string, path: string, thumbnail_path: ?string, filename: string, mime: string, size: int, width: int, height: int}
     */
    private function processAndStore(string $absolutePath, string $collection, string $originalName, array $options): array
    {
        $collection = $this->normalizeCollection($collection);
        $this->assertImageFile($absolutePath, $originalName);

        $processed = $this->encodeWebp($absolutePath, (int) config('image.quality', 80), (int) config('image.max_width', 1920), (int) config('image.max_height', 1920));
        $filename = $this->uniqueFilename($collection, $originalName, $options['prefix'] ?? null);
        $path = $collection.'/'.$filename;

        $disk = Storage::disk($this->disk());
        $disk->put($path, $processed['binary']);

        $thumbnailPath = null;
        if ($this->shouldThumbnail($collection)) {
            $thumb = $this->encodeWebp($absolutePath, (int) config('image.thumbnail_quality', 75), (int) config('image.thumbnail_width', 400), 0);
            $thumbnailPath = $this->thumbnailPathFor($path);
            $disk->put($thumbnailPath, $thumb['binary']);
        }

        $size = $disk->size($path) ?: strlen($processed['binary']);

        return [
            'disk' => $this->disk(),
            'path' => $path,
            'thumbnail_path' => $thumbnailPath,
            'filename' => $filename,
            'mime' => 'image/webp',
            'size' => $size,
            'width' => $processed['width'],
            'height' => $processed['height'],
        ];
    }

    private function assertUpload(UploadedFile $file): void
    {
        if (! $file->isValid()) {
            throw ImageException::invalid('بارگذاری فایل ناموفق بود.');
        }

        $maxKb = (int) config('image.max_kilobytes', 51200);
        if ($file->getSize() > $maxKb * 1024) {
            throw ImageException::tooLarge();
        }

        $original = strtolower($file->getClientOriginalName());
        if (preg_match('/\.(php|phtml|phar|exe|js|html|htm|shtml|cgi|pl)$/i', $original)) {
            throw ImageException::unsupported();
        }
    }

    private function assertImageFile(string $path, string $originalName): void
    {
        $mime = $this->detectMime($path);
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedMimes = $this->allowedMimes();
        $allowedExt = $this->allowedExtensions();

        if (! in_array($mime, $allowedMimes, true) && ! in_array($ext, $allowedExt, true)) {
            throw ImageException::unsupported();
        }

        $info = @getimagesize($path);
        if (! is_array($info) || empty($info[0]) || empty($info[1])) {
            if (! $this->imagickCanRead($path)) {
                throw ImageException::invalid();
            }
            $info = $this->imagickDimensions($path);
        }

        $pixels = ((int) $info[0]) * ((int) $info[1]);
        if ($pixels > (int) config('image.max_pixels', 40000000)) {
            throw ImageException::tooLarge();
        }
    }

    /**
     * @return array{binary: string, width: int, height: int}
     */
    private function encodeWebp(string $path, int $quality, int $maxWidth, int $maxHeight): array
    {
        $quality = max(1, min(100, $quality));

        try {
            return $this->encodeWithGd($path, $quality, $maxWidth, $maxHeight);
        } catch (Throwable $gdError) {
            if ($this->imagickAvailable()) {
                try {
                    return $this->encodeWithImagick($path, $quality, $maxWidth, $maxHeight);
                } catch (Throwable) {
                    // fall through to original GD error
                }
            }

            if ($gdError instanceof ImageException) {
                throw $gdError;
            }

            throw ImageException::processingFailed();
        }
    }

    /**
     * @return array{binary: string, width: int, height: int}
     */
    private function encodeWithGd(string $path, int $quality, int $maxWidth, int $maxHeight): array
    {
        if (! function_exists('imagewebp') || ! function_exists('imagecreatetruecolor')) {
            throw ImageException::processingFailed();
        }

        $image = $this->loadGdImage($path);
        if ($image === false) {
            throw ImageException::unsupported();
        }

        if (function_exists('imagepalettetotruecolor') && ! imageistruecolor($image) && ! imagepalettetotruecolor($image)) {
            imagedestroy($image);
            throw ImageException::processingFailed();
        }

        $width = imagesx($image);
        $height = imagesy($image);
        [$targetW, $targetH] = $this->fitSize($width, $height, $maxWidth, $maxHeight);

        if ($targetW !== $width || $targetH !== $height) {
            $resized = imagecreatetruecolor($targetW, $targetH);
            if ($resized === false) {
                imagedestroy($image);
                throw ImageException::processingFailed();
            }
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefilledrectangle($resized, 0, 0, $targetW, $targetH, $transparent);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $targetW, $targetH, $width, $height);
            imagedestroy($image);
            $image = $resized;
            $width = $targetW;
            $height = $targetH;
        } else {
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }

        $tmp = $this->tempFile('webp');
        try {
            if (! imagewebp($image, $tmp, $quality)) {
                throw ImageException::processingFailed();
            }
            $binary = (string) file_get_contents($tmp);
            if ($binary === '') {
                throw ImageException::processingFailed();
            }
        } finally {
            imagedestroy($image);
            @unlink($tmp);
        }

        return [
            'binary' => $binary,
            'width' => $width,
            'height' => $height,
        ];
    }

    /**
     * @return array{binary: string, width: int, height: int}
     */
    private function encodeWithImagick(string $path, int $quality, int $maxWidth, int $maxHeight): array
    {
        $imagick = new \Imagick($path);
        if ($imagick->getNumberImages() > 1) {
            $imagick = $imagick->coalesceImages();
            $imagick->setIteratorIndex(0);
        }

        $width = $imagick->getImageWidth();
        $height = $imagick->getImageHeight();
        [$targetW, $targetH] = $this->fitSize($width, $height, $maxWidth, $maxHeight);
        if ($targetW !== $width || $targetH !== $height) {
            $imagick->resizeImage($targetW, $targetH, \Imagick::FILTER_LANCZOS, 1, true);
            $width = $imagick->getImageWidth();
            $height = $imagick->getImageHeight();
        }

        $imagick->setImageFormat('webp');
        $imagick->setImageCompressionQuality($quality);
        $binary = $imagick->getImagesBlob();
        $imagick->clear();
        $imagick->destroy();

        if (! is_string($binary) || $binary === '') {
            throw ImageException::processingFailed();
        }

        return [
            'binary' => $binary,
            'width' => $width,
            'height' => $height,
        ];
    }

    private function loadGdImage(string $path): \GdImage|false
    {
        $mime = $this->detectMime($path);
        $info = @getimagesize($path);
        $type = is_array($info) ? ($info[2] ?? null) : null;

        $image = match (true) {
            $mime === 'image/jpeg' || $type === IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            $mime === 'image/png' || $type === IMAGETYPE_PNG => @imagecreatefrompng($path),
            $mime === 'image/gif' || $type === IMAGETYPE_GIF => @imagecreatefromgif($path),
            $mime === 'image/webp' || $type === IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            $mime === 'image/bmp' || $mime === 'image/x-ms-bmp' || $type === IMAGETYPE_BMP => function_exists('imagecreatefrombmp') ? @imagecreatefrombmp($path) : false,
            default => false,
        };

        if ($image instanceof \GdImage) {
            return $image;
        }

        $raw = @file_get_contents($path);
        if ($raw === false || $raw === '') {
            return false;
        }

        return @imagecreatefromstring($raw);
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function fitSize(int $width, int $height, int $maxWidth, int $maxHeight): array
    {
        $width = max(1, $width);
        $height = max(1, $height);
        $scale = 1;

        if ($maxWidth > 0 && $width > $maxWidth) {
            $scale = min($scale, $maxWidth / $width);
        }
        if ($maxHeight > 0 && $height > $maxHeight) {
            $scale = min($scale, $maxHeight / $height);
        }

        if ($scale >= 1) {
            return [$width, $height];
        }

        return [
            max(1, (int) round($width * $scale)),
            max(1, (int) round($height * $scale)),
        ];
    }

    private function uniqueFilename(string $collection, string $originalName, ?string $prefix = null): string
    {
        $prefixes = config('image.collections', []);
        $fallback = $prefixes[$collection] ?? $collection;
        $slug = Str::slug(pathinfo($originalName, PATHINFO_FILENAME));
        $base = $prefix ?: ($slug !== '' ? $slug : $fallback);
        $base = strtolower((string) preg_replace('/[^a-z0-9\-]+/', '', str_replace(' ', '-', $base)));
        $base = trim($base, '-');
        if ($base === '') {
            $base = $fallback;
        }
        $base = substr($base, 0, 40);

        $disk = Storage::disk($this->disk());
        do {
            $name = $base.'-'.bin2hex(random_bytes(3)).'.webp';
            $path = $collection.'/'.$name;
        } while ($disk->exists($path));

        return $name;
    }

    private function shouldThumbnail(string $collection): bool
    {
        return in_array($collection, config('image.thumbnails', []), true);
    }

    private function detectMime(string $path): string
    {
        if ($path === '' || ! is_file($path)) {
            return '';
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = strtolower((string) $finfo->file($path));

        return explode(';', $mime)[0];
    }

    private function allowedMimes(): array
    {
        return config('image.mimes', []);
    }

    private function allowedExtensions(): array
    {
        return config('image.extensions', []);
    }

    private function disk(): string
    {
        return (string) config('image.disk', 'public');
    }

    /**
     * @return array{0: string, 1: bool}
     */
    private function resolveReadablePath(string $path): array
    {
        if (is_file($path)) {
            return [$path, false];
        }

        $relative = $this->normalizeRelativePath($path);
        $disk = Storage::disk($this->disk());
        if ($relative !== '' && $disk->exists($relative)) {
            $tmp = $this->tempFile('src');
            file_put_contents($tmp, $disk->get($relative));

            return [$tmp, true];
        }

        $site = base_path(str_replace('/', DIRECTORY_SEPARATOR, $relative));
        if ($relative !== '' && is_file($site)) {
            return [$site, false];
        }

        throw ImageException::invalid('فایل تصویر پیدا نشد.');
    }

    private function normalizeRelativePath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        $path = preg_replace('#^/+#', '', $path) ?? $path;
        if (str_starts_with($path, 'storage/')) {
            $path = substr($path, strlen('storage/'));
        }
        if (str_contains($path, '..')) {
            return '';
        }

        return ltrim($path, '/');
    }

    private function isProtectedSitePath(string $path): bool
    {
        return str_starts_with($path, 'images/')
            || str_starts_with($path, 'assets/')
            || str_starts_with($path, 'data/');
    }

    private function imagickAvailable(): bool
    {
        return class_exists(\Imagick::class);
    }

    private function imagickCanRead(string $path): bool
    {
        if (! $this->imagickAvailable()) {
            return false;
        }
        try {
            $im = new \Imagick($path);

            return $im->getImageWidth() > 0;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function imagickDimensions(string $path): array
    {
        $im = new \Imagick($path);

        return [$im->getImageWidth(), $im->getImageHeight()];
    }

    private function tempFile(string $suffix): string
    {
        $path = tempnam(sys_get_temp_dir(), 'alwin-'.$suffix.'-');
        if ($path === false) {
            throw ImageException::processingFailed();
        }

        return $path;
    }
}
