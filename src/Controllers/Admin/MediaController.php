<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Exceptions\ImageException;
use App\Models\Media;
use App\Services\ImageService;
use App\Support\Audit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\View\View;

class MediaController extends Controller
{
    private const VIDEO_MIMES = ['video/mp4', 'video/webm'];

    private const VIDEO_EXT = ['mp4', 'webm'];

    public function __construct(private ImageService $images)
    {
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Media::class);
        $q = Media::query();
        if ($request->filled('search')) {
            $s = $request->string('search')->toString();
            $q->where(function ($qq) use ($s) {
                $qq->where('filename', 'like', "%{$s}%")
                    ->orWhere('original_name', 'like', "%{$s}%")
                    ->orWhere('alt', 'like', "%{$s}%");
            });
        }
        if ($request->filled('kind')) {
            $q->where('kind', $request->string('kind'));
        }

        return view('admin.media.index', [
            'items' => $q->latest()->paginate(24)->withQueryString(),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', Media::class);
        $request->validate([
            'file' => ['required', 'file', 'max:'.(int) config('image.max_kilobytes', 51200)],
            'alt' => ['nullable', 'string', 'max:190'],
            'collection' => ['nullable', 'string', 'max:32'],
        ], [
            'file.required' => 'فایل را انتخاب کنید.',
            'file.max' => 'حجم فایل بیش از حد مجاز است.',
        ]);

        $file = $request->file('file');
        try {
            $media = $this->createFromUpload(
                $file,
                $request->string('alt')->toString(),
                $request->user()->id,
                $request->string('collection')->toString()
            );
        } catch (ImageException $e) {
            return $this->uploadFailed($request, $e->getMessage());
        }

        Audit::log('upload', $media, 'بارگذاری فایل '.$media->original_name);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['ok' => true, 'media' => $this->payload($media)]);
        }

        return back()->with('success', 'فایل بارگذاری شد.');
    }

    public function update(Request $request, Media $medium): RedirectResponse
    {
        $this->authorize('update', $medium);
        $data = $request->validate([
            'alt' => ['nullable', 'string', 'max:190'],
            'file' => ['nullable', 'file', 'max:'.(int) config('image.max_kilobytes', 51200)],
            'collection' => ['nullable', 'string', 'max:32'],
        ]);
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $oldPath = $medium->disk === 'public' ? $medium->path : null;
            try {
                $attrs = $this->storedAttributes(
                    $file,
                    $request->string('collection')->toString() ?: $medium->guessCollection()
                );
            } catch (ImageException $e) {
                return back()->withErrors(['file' => $e->getMessage()]);
            }
            $medium->fill($attrs);
            $medium->save();
            if ($oldPath && $oldPath !== $medium->path) {
                $this->images->delete($oldPath);
            }
        }
        $medium->alt = $data['alt'] ?? $medium->alt;
        $medium->save();
        Audit::log('update', $medium, 'ویرایش رسانه '.$medium->filename);

        return back()->with('success', 'رسانه به‌روز شد.');
    }

    public function destroy(Media $medium): RedirectResponse
    {
        if ($medium->isUsed()) {
            return back()->withErrors(['media' => 'این فایل در محتوا استفاده شده و قابل حذف نیست.']);
        }
        $this->authorize('delete', $medium);
        if ($medium->disk === 'public') {
            $this->images->delete($medium->path);
        }
        $medium->forceDelete();
        Audit::log('delete', $medium, 'حذف رسانه '.$medium->filename);

        return back()->with('success', 'فایل حذف شد.');
    }

    private function createFromUpload(UploadedFile $file, string $alt, int $userId, string $collection): Media
    {
        $attrs = $this->storedAttributes($file, $collection);
        $attrs['original_name'] = $file->getClientOriginalName();
        $attrs['alt'] = $alt !== '' ? $alt : pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $attrs['uploaded_by'] = $userId;

        return Media::query()->create($attrs);
    }

    /**
     * @return array<string, mixed>
     */
    private function storedAttributes(UploadedFile $file, string $collection): array
    {
        if ($this->isVideo($file)) {
            return $this->storeVideo($file);
        }

        if (! $this->images->isImageUpload($file)) {
            throw ImageException::unsupported();
        }

        $stored = $this->images->store($file, $collection);

        return [
            'disk' => $stored['disk'],
            'path' => $stored['path'],
            'filename' => $stored['filename'],
            'mime' => $stored['mime'],
            'size' => $stored['size'],
            'kind' => 'image',
            'width' => $stored['width'],
            'height' => $stored['height'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function storeVideo(UploadedFile $file): array
    {
        $ext = strtolower($file->getClientOriginalExtension());
        $mime = $this->realMime($file);
        $original = strtolower($file->getClientOriginalName());
        if (
            ! in_array($ext, self::VIDEO_EXT, true)
            || ! in_array($mime, self::VIDEO_MIMES, true)
            || preg_match('/\.(php|phtml|phar|exe|js|html|htm|shtml)$/i', $original)
        ) {
            throw ImageException::unsupported();
        }

        $dir = 'media/'.date('Y/m');
        $stored = $file->storeAs($dir, uniqid('', true).'.'.$ext, 'public');

        return [
            'disk' => 'public',
            'path' => $stored,
            'filename' => basename($stored),
            'mime' => $mime,
            'size' => $file->getSize(),
            'kind' => 'video',
            'width' => null,
            'height' => null,
        ];
    }

    private function isVideo(UploadedFile $file): bool
    {
        $mime = $this->realMime($file);
        $ext = strtolower($file->getClientOriginalExtension());

        return str_starts_with($mime, 'video/') || in_array($ext, self::VIDEO_EXT, true);
    }

    private function realMime(UploadedFile $file): string
    {
        $path = $file->getRealPath();
        if (! is_string($path) || $path === '') {
            return (string) $file->getMimeType();
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = strtolower((string) $finfo->file($path));

        return explode(';', $mime)[0] ?: (string) $file->getMimeType();
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Media $media): array
    {
        return [
            'id' => $media->id,
            'url' => $media->publicUrl(),
            'thumb_url' => $media->thumbnailUrl(),
            'alt' => $media->alt,
            'filename' => $media->original_name,
            'name' => $media->original_name,
            'kind' => $media->kind,
        ];
    }

    private function uploadFailed(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message' => $message,
                'errors' => ['file' => [$message]],
            ], 422);
        }

        return back()->withErrors(['file' => $message]);
    }
}
