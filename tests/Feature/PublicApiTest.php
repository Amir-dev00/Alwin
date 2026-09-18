<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_home_and_article_endpoints(): void
    {
        $home = $this->getJson('/api/v1/home')->assertOk();
        $home->assertJsonStructure(['projects', 'products', 'partners', 'project_count', 'product_count']);
        $this->assertGreaterThan(0, $home->json('project_count'));

        $this->getJson('/api/home')->assertOk()->assertJsonPath('project_count', $home->json('project_count'));

        $project = Project::query()->where('status', 'published')->first();
        $this->assertNotNull($project);
        $this->getJson('/api/v1/projects/'.$project->slug)
            ->assertOk()
            ->assertJsonPath('project.slug', $project->slug)
            ->assertJsonStructure(['prev', 'next']);

        $article = Article::query()->published()->first();
        if (! $article) {
            $this->markTestSkipped('No published article in seed.');
        }

        $this->getJson('/api/v1/articles')->assertOk()->assertJsonStructure(['articles', 'meta']);
        $this->getJson('/api/v1/articles/'.$article->slug)
            ->assertOk()
            ->assertJsonPath('article.slug', $article->slug)
            ->assertJsonStructure(['article' => ['body'], 'more']);
    }

    public function test_unlisted_articles_are_hidden_from_listing(): void
    {
        $article = Article::query()->create([
            'title' => 'مقاله خارج از فهرست',
            'slug' => 'unlisted-article-test',
            'excerpt' => 'فقط از لینک مستقیم باز می‌شود.',
            'status' => 'published',
            'published_at' => now(),
            'show_on_listing' => false,
        ]);

        $slugs = collect($this->getJson('/api/v1/articles')->assertOk()->json('articles'))->pluck('slug');
        $this->assertFalse($slugs->contains($article->slug));
        $this->getJson('/api/v1/articles/'.$article->slug)->assertOk()->assertJsonPath('article.slug', $article->slug);
    }
}
