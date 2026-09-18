<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CmsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_page_is_public_and_registration_is_absent(): void
    {
        $this->get('/admin/login')->assertOk()->assertSee('ورود به پنل مدیریت');
        $this->get('/register')->assertNotFound();
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_super_admin_can_login_and_see_dashboard(): void
    {
        $this->from('/admin/login')->post('/admin/login', [
            'email' => env('ADMIN_EMAIL', 'admin@alwinco.ir'),
            'password' => env('ADMIN_PASSWORD', 'ChangeMe!Alwin2026'),
        ])->assertRedirect('/admin');

        $this->get('/admin')->assertOk()->assertSee('نمای کلی');
    }

    public function test_new_product_autofills_slug_and_seo_from_name(): void
    {
        $admin = User::query()->where('role', User::ROLE_SUPER_ADMIN)->first();

        $this->actingAs($admin)->post('/admin/products', [
            'name' => 'پنجره دوجداره آزمایشی',
            'status' => 'published',
            'short_description' => 'عایق صدا و حرارت برای آپارتمان.',
        ])->assertRedirect();

        $item = Product::query()->where('name', 'پنجره دوجداره آزمایشی')->first();
        $this->assertNotNull($item);
        $this->assertNotEmpty($item->slug);
        $this->assertStringContainsString('پنجره دوجداره آزمایشی', $item->seo_title);
        $this->assertStringContainsString('عایق صدا', $item->seo_description);
        $this->assertSame('پنجره دوجداره آزمایشی', $item->alt_text);
    }

    public function test_login_is_rate_limited(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->from('/admin/login')->post('/admin/login', [
                'email' => 'nobody@alwinco.ir',
                'password' => 'wrong-password',
            ]);
        }

        $this->from('/admin/login')->post('/admin/login', [
            'email' => 'nobody@alwinco.ir',
            'password' => 'wrong-password',
        ])->assertStatus(429);
    }

    public function test_public_api_returns_seeded_catalog(): void
    {
        $products = $this->getJson('/api/v1/products')->assertOk()->json('products');
        $projects = $this->getJson('/api/v1/projects')->assertOk()->json('projects');
        $site = $this->getJson('/api/v1/site')->assertOk();

        $this->assertCount(15, $products);
        $this->assertCount(13, $projects);
        $site->assertJsonPath('settings.email', 'info@alwinco.ir');
        $this->assertNotEmpty($site->json('pages.home.blocks.hero_title'));
        $this->assertCount(6, $site->json('partners'));
    }

    public function test_product_update_appears_on_api_immediately(): void
    {
        $admin = User::query()->where('role', User::ROLE_SUPER_ADMIN)->first();
        $product = Product::query()->first();

        $this->actingAs($admin)->put('/admin/products/'.$product->id, [
            'name' => 'پنجره آزمایشی آلوین',
            'slug' => 'test-alwin-window',
            'status' => 'published',
            'short_description' => '<strong>ضخیم</strong><script>alert(1)</script>',
            'show_on_home' => '1',
            'show_on_listing' => '1',
            'sort_order' => 1,
        ])->assertRedirect();

        $payload = $this->getJson('/api/v1/products')->json('products');
        $row = collect($payload)->firstWhere('slug', 'test-alwin-window');
        $this->assertNotNull($row);
        $this->assertSame('پنجره آزمایشی آلوین', $row['title']);
        $this->assertStringContainsString('<strong>', $row['short_description']);
        $this->assertStringNotContainsString('<script>', $row['short_description']);
    }

    public function test_editor_cannot_manage_users(): void
    {
        $editor = User::factory()->create(['role' => User::ROLE_EDITOR]);
        $this->actingAs($editor)->get('/admin/users')->assertForbidden();
        $this->actingAs($editor)->get('/admin/products')->assertOk();
    }

    public function test_project_and_page_updates_hit_public_api(): void
    {
        $admin = User::query()->where('role', User::ROLE_SUPER_ADMIN)->first();
        $project = \App\Models\Project::query()->first();
        $page = \App\Models\Page::query()->where('key', 'home')->first();
        $block = $page->blocks()->first();

        $this->actingAs($admin)->put('/admin/projects/'.$project->id, [
            'title' => 'پروژه ویرایش‌شده',
            'slug' => $project->slug,
            'status' => 'published',
            'type' => 'upvc',
            'type_label' => 'پنجره UPVC',
            'show_on_home' => '1',
            'show_on_listing' => '1',
            'sort_order' => 1,
        ])->assertRedirect();

        $this->actingAs($admin)->put('/admin/pages/'.$page->id, [
            'title' => $page->title,
            'seo_title' => 'عنوان سئو جدید',
            'blocks' => [
                ['id' => $block->id, 'value' => 'متن تازه هیرو'],
            ],
        ])->assertRedirect();

        $this->getJson('/api/v1/projects')->assertJsonFragment(['title' => 'پروژه ویرایش‌شده']);
        $this->getJson('/api/v1/site')->assertJsonPath('pages.home.seo.title', 'عنوان سئو جدید');
    }

    public function test_about_calling_form_notifies_admin_of_new_order(): void
    {
        $this->postJson('/api/v1/contact', [
            'name' => 'مریم تست',
            'phone' => '09120000000',
            'subject' => 'callback',
            'source' => 'about',
            'message' => 'لطفاً برای سفارش پنجره تماس بگیرید.',
        ])->assertCreated()->assertJsonPath('ok', true);

        $this->assertDatabaseHas('contact_inquiries', [
            'name' => 'مریم تست',
            'phone' => '09120000000',
            'source' => 'about',
            'status' => 'new',
        ]);

        $admin = User::query()->where('role', User::ROLE_SUPER_ADMIN)->first();
        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('درخواست تماس جدید')
            ->assertSee('سفارش‌ها');

        $this->actingAs($admin)
            ->get('/admin/inquiries')
            ->assertOk()
            ->assertSee('مریم تست')
            ->assertSee('09120000000')
            ->assertSee('صفحه درباره ما');
    }

    public function test_admin_can_toggle_product_and_project_listing(): void
    {
        $admin = User::query()->where('role', User::ROLE_SUPER_ADMIN)->first();
        $product = Product::query()->orderBy('sort_order')->first();
        $project = \App\Models\Project::query()->orderBy('sort_order')->first();
        $this->assertNotNull($product);
        $this->assertNotNull($project);

        $this->actingAs($admin)
            ->from('/admin/products')
            ->post('/admin/products/'.$product->id.'/listing', ['show_on_listing' => '0'])
            ->assertRedirect('/admin/products');
        $this->assertFalse($product->fresh()->show_on_listing);

        $this->actingAs($admin)
            ->from('/admin/projects')
            ->post('/admin/projects/'.$project->id.'/listing', ['show_on_listing' => '0'])
            ->assertRedirect('/admin/projects');
        $this->assertFalse($project->fresh()->show_on_listing);

        $productSlugs = collect($this->getJson('/api/v1/products')->json('products'))->pluck('slug');
        $projectSlugs = collect($this->getJson('/api/v1/projects')->json('projects'))->pluck('slug');
        $this->assertFalse($productSlugs->contains($product->slug));
        $this->assertFalse($projectSlugs->contains($project->slug));
    }

    public function test_admin_can_toggle_article_listing(): void
    {
        $admin = User::query()->where('role', User::ROLE_SUPER_ADMIN)->first();
        $article = Article::query()->published()->latest('published_at')->first();
        if (! $article) {
            $this->markTestSkipped('No published article in seed.');
        }

        $this->actingAs($admin)
            ->from('/admin/articles')
            ->post('/admin/articles/'.$article->slug.'/listing', ['show_on_listing' => '0'])
            ->assertRedirect('/admin/articles');

        $this->assertFalse($article->fresh()->show_on_listing);
        $this->actingAs($admin)->get('/admin/articles')->assertOk()->assertSee('افزودن به فهرست');
    }

    public function test_executable_uploads_are_rejected(): void
    {
        Storage::fake('public');
        $admin = User::query()->where('role', User::ROLE_SUPER_ADMIN)->first();
        $file = UploadedFile::fake()->create('shell.php', 20, 'application/x-php');

        $this->actingAs($admin)->post('/admin/media', [
            'file' => $file,
        ])->assertSessionHasErrors('file');
    }

    public function test_admin_homepage_opens_home_editor(): void
    {
        $admin = User::query()->where('role', User::ROLE_SUPER_ADMIN)->first();
        $home = \App\Models\Page::query()->where('key', 'home')->first();

        $this->actingAs($admin)
            ->get('/admin/homepage')
            ->assertRedirect(route('admin.pages.edit', $home));
    }

    public function test_calculator_copy_updates_public_site_immediately(): void
    {
        $admin = User::query()->where('role', User::ROLE_SUPER_ADMIN)->first();
        $this->actingAs($admin)
            ->get('/admin/settings?screen=calculator')
            ->assertOk()
            ->assertSee('متن ماشین‌حساب');

        $row = \App\Models\Setting::query()->where('key', 'calc_title')->first();
        $this->assertNotNull($row);

        $this->actingAs($admin)->put('/admin/settings?screen=calculator', [
            'settings' => [$row->id => 'برآورد آنلاین آزمایشی'],
        ])->assertRedirect();

        $this->get('/')->assertOk()->assertSee('برآورد آنلاین آزمایشی');
    }

    public function test_homepage_client_title_is_managed_from_admin(): void
    {
        $admin = User::query()->where('role', User::ROLE_SUPER_ADMIN)->first();
        $page = \App\Models\Page::query()->where('key', 'home')->first();
        $block = $page->blocks()->where('key', 'clients_marquee_title')->first();
        $this->assertNotNull($block);

        $this->actingAs($admin)->put('/admin/pages/'.$page->id, [
            'title' => $page->title,
            'seo_title' => $page->seo_title,
            'blocks' => [
                ['id' => $block->id, 'value' => 'همکاران آزمایشی آلوین'],
            ],
        ])->assertRedirect();

        $this->get('/')->assertOk()->assertSee('همکاران آزمایشی آلوین');
    }

    public function test_project_detail_cta_is_managed_from_admin(): void
    {
        $admin = User::query()->where('role', User::ROLE_SUPER_ADMIN)->first();
        $page = \App\Models\Page::query()->where('key', 'project')->first();
        $block = $page->blocks()->where('key', 'cta_html')->first();
        $this->assertNotNull($block);

        $this->actingAs($admin)->put('/admin/pages/'.$page->id, [
            'title' => $page->title,
            'blocks' => [
                ['id' => $block->id, 'value' => 'استعلام<br>قیمت'],
            ],
        ])->assertRedirect();

        $project = \App\Models\Project::query()->where('status', 'published')->first();
        $this->get('/projects/'.$project->slug)->assertOk()->assertSee('استعلام');
    }

    public function test_editor_can_edit_settings_and_seo_screen(): void
    {
        $editor = User::factory()->create(['role' => User::ROLE_EDITOR]);
        $this->actingAs($editor)->get('/admin/settings')->assertOk();
        $this->actingAs($editor)->get('/admin/settings?screen=seo')->assertOk()->assertSee('سئو پیش‌فرض');
    }
}
