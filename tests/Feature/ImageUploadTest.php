<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Media;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('public');
    }

    public function test_jpg_upload_is_stored_as_webp_without_original(): void
    {
        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('GD WebP support is required.');
        }

        $admin = User::query()->where('role', User::ROLE_SUPER_ADMIN)->first();
        $file = UploadedFile::fake()->image('My Product FINAL IMAGE!!.jpg', 800, 600);

        $this->actingAs($admin)->post('/admin/media', [
            'file' => $file,
            'collection' => 'products',
        ])->assertRedirect();

        $media = Media::query()->latest('id')->first();
        $this->assertNotNull($media);
        $this->assertSame('image', $media->kind);
        $this->assertSame('image/webp', $media->mime);
        $this->assertSame('public', $media->disk);
        $this->assertStringEndsWith('.webp', $media->path);
        $this->assertStringStartsWith('products/', $media->path);
        $this->assertStringNotContainsString(' ', $media->filename);
        $this->assertDoesNotMatchRegularExpression('/[A-Z]/', $media->filename);

        Storage::disk('public')->assertExists($media->path);
        Storage::disk('public')->assertExists('products/thumbs/'.$media->filename);
        $this->assertFalse(Storage::disk('public')->exists('products/'.pathinfo($file->getClientOriginalName(), PATHINFO_BASENAME)));
        $this->assertEmpty(collect(Storage::disk('public')->allFiles('products'))->filter(
            fn (string $path) => str_ends_with(strtolower($path), '.jpg')
        ));
    }

    public function test_png_upload_is_resized_when_larger_than_max(): void
    {
        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('GD WebP support is required.');
        }

        $admin = User::query()->where('role', User::ROLE_SUPER_ADMIN)->first();
        $file = UploadedFile::fake()->image('wide.png', 2400, 1200);

        $this->actingAs($admin)->postJson('/admin/media', [
            'file' => $file,
            'collection' => 'projects',
        ])->assertOk()->assertJsonPath('media.kind', 'image');

        $media = Media::query()->latest('id')->first();
        $this->assertSame(1920, $media->width);
        $this->assertSame(960, $media->height);
        $this->assertStringStartsWith('projects/', $media->path);
    }

    public function test_small_images_are_not_enlarged(): void
    {
        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('GD WebP support is required.');
        }

        $admin = User::query()->where('role', User::ROLE_SUPER_ADMIN)->first();
        $file = UploadedFile::fake()->image('tiny.png', 320, 200);

        $this->actingAs($admin)->post('/admin/media', [
            'file' => $file,
        ])->assertRedirect();

        $media = Media::query()->latest('id')->first();
        $this->assertSame(320, $media->width);
        $this->assertSame(200, $media->height);
    }

    public function test_replacing_media_deletes_old_webp_after_success(): void
    {
        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('GD WebP support is required.');
        }

        $admin = User::query()->where('role', User::ROLE_SUPER_ADMIN)->first();
        $this->actingAs($admin)->post('/admin/media', [
            'file' => UploadedFile::fake()->image('first.jpg', 400, 300),
            'collection' => 'media',
        ])->assertRedirect();

        $media = Media::query()->latest('id')->first();
        $oldPath = $media->path;
        Storage::disk('public')->assertExists($oldPath);

        $this->actingAs($admin)->put('/admin/media/'.$media->id, [
            'alt' => 'replaced',
            'file' => UploadedFile::fake()->image('second.png', 500, 400),
        ])->assertRedirect();

        $media->refresh();
        $this->assertNotSame($oldPath, $media->path);
        $this->assertStringEndsWith('.webp', $media->path);
        Storage::disk('public')->assertExists($media->path);
        Storage::disk('public')->assertMissing($oldPath);
    }

    public function test_article_cover_upload_stores_webp_path(): void
    {
        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('GD WebP support is required.');
        }

        $admin = User::query()->where('role', User::ROLE_SUPER_ADMIN)->first();

        $this->actingAs($admin)->post('/admin/articles', [
            'title' => 'مقاله تصویر',
            'status' => 'published',
            'cover' => UploadedFile::fake()->image('cover.jpeg', 640, 360),
        ])->assertRedirect();

        $article = Article::query()->where('title', 'مقاله تصویر')->first();
        $this->assertNotNull($article);
        $this->assertStringStartsWith('articles/', $article->cover_path);
        $this->assertStringEndsWith('.webp', $article->cover_path);
        Storage::disk('public')->assertExists($article->cover_path);
        $this->assertStringContainsString('/storage/articles/', (string) $article->coverUrl());
    }

    public function test_image_service_rejects_non_images(): void
    {
        $this->expectException(\App\Exceptions\ImageException::class);
        $service = app(ImageService::class);
        $service->store(UploadedFile::fake()->create('notes.txt', 10, 'text/plain'), 'media');
    }

    public function test_convert_webp_command_is_registered(): void
    {
        $this->artisan('images:convert-webp')->assertExitCode(0);
    }
}
