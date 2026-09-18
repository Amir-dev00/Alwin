<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitePagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_public_pages_render(): void
    {
        $this->get('/')->assertOk()->assertSee('ALWIN');
        $this->get('/about')->assertOk()->assertSee('callingForm');
        $this->get('/products')->assertOk();
        $this->get('/projects')->assertOk();
        $this->get('/articles')->assertOk();
        $this->get('/contact')->assertOk()->assertSee('contactForm');
    }

    public function test_legacy_html_urls_redirect(): void
    {
        $this->get('/index.html')->assertRedirect('/');
        $this->get('/about.html')->assertRedirect('/about');
        $this->get('/services.html')->assertRedirect('/products');
        $this->get('/contact.html')->assertRedirect('/contact');
        $this->get('/portfolio.html')->assertRedirect('/projects');
    }

    public function test_project_detail_and_legacy_query_redirect(): void
    {
        $project = Project::query()->where('status', 'published')->first();
        $this->assertNotNull($project);
        $this->get('/projects/'.$project->slug)->assertOk()->assertSee($project->title);
        $this->get('/project.html?slug='.urlencode($project->slug))
            ->assertRedirect(route('projects.show', $project, absolute: false));
    }

    public function test_article_show_when_imported(): void
    {
        $article = Article::query()->published()->latest('published_at')->first();
        if (! $article) {
            $this->markTestSkipped('Article HTML source was not available to import.');
        }
        $this->get('/articles')->assertSee($article->title);
        $this->get('/articles/'.$article->slug)->assertOk()->assertSee($article->title);
        $this->get('/articles/'.$article->slug.'/index.html')
            ->assertRedirect(route('articles.show', $article, absolute: false));
    }

    public function test_article_listing_covers_load_including_persian_filenames(): void
    {
        $articles = Article::query()->published()->listed()->get();
        $this->assertGreaterThan(1, $articles->count());

        $listing = $this->get('/articles')->assertOk();
        $withCover = 0;
        foreach ($articles as $article) {
            $url = $article->coverUrl();
            if (! $url) {
                continue;
            }
            $withCover++;
            $listing->assertSee($url, false);
            $response = $this->get($url)->assertOk();
            $this->assertStringStartsWith('image/', (string) $response->headers->get('content-type'));
        }

        $this->assertGreaterThan(1, $withCover);
    }
}
