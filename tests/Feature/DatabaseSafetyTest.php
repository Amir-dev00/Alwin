<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Models\PageBlock;
use App\Models\PricingGlass;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Support\MysqlDumper;
use Database\Seeders\CmsContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DatabaseSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_reseed_does_not_overwrite_existing_content(): void
    {
        $admin = User::query()->where('email', env('ADMIN_EMAIL', 'admin@alwinco.ir'))->first();
        $admin->name = 'ادمین زنده';
        $admin->password = 'KeepThisPassword!Alwin';
        $admin->save();

        $product = Product::query()->orderBy('id')->first();
        $product->name = 'محصول ویرایش‌شده تولید';
        $product->save();

        $setting = Setting::query()->where('key', 'email')->first();
        $setting->value = 'kept@alwinco.ir';
        $setting->save();

        $nav = NavigationItem::query()->where('location', 'header')->where('url', '/')->first();
        $nav->label = 'خانه سفارشی';
        $nav->save();

        $page = Page::query()->where('key', 'home')->first();
        $page->title = 'عنوان صفحه زنده';
        $page->save();

        $block = PageBlock::query()->where('page_id', $page->id)->where('key', 'hero_title')->first();
        $block->value = 'هیروی ویرایش‌شده';
        $block->save();

        $article = Article::query()->orderBy('id')->first();
        $originalArticleTitle = $article->title;
        $article->title = 'مقاله ویرایش‌شده تولید';
        $article->save();

        $glass = PricingGlass::query()->where('key', 'simple')->first();
        $glass->price_per_sqm = 123456;
        $glass->save();

        $counts = [
            'users' => User::query()->count(),
            'products' => Product::query()->count(),
            'articles' => Article::query()->count(),
            'nav' => NavigationItem::query()->count(),
        ];

        $this->seed();
        $this->seed(CmsContentSeeder::class);

        $this->assertSame($counts['users'], User::query()->count());
        $this->assertSame($counts['products'], Product::query()->count());
        $this->assertSame($counts['articles'], Article::query()->count());
        $this->assertSame($counts['nav'], NavigationItem::query()->count());

        $admin->refresh();
        $this->assertSame('ادمین زنده', $admin->name);
        $this->assertTrue(Hash::check('KeepThisPassword!Alwin', $admin->password));

        $this->assertSame('محصول ویرایش‌شده تولید', $product->fresh()->name);
        $this->assertSame('kept@alwinco.ir', Setting::query()->where('key', 'email')->value('value'));
        $this->assertSame('خانه سفارشی', $nav->fresh()->label);
        $this->assertSame('عنوان صفحه زنده', $page->fresh()->title);
        $this->assertSame('هیروی ویرایش‌شده', $block->fresh()->value);
        $this->assertSame('مقاله ویرایش‌شده تولید', $article->fresh()->title);
        $this->assertNotSame($originalArticleTitle, $article->fresh()->title);
        $this->assertSame(123456, (int) $glass->fresh()->price_per_sqm);
    }

    public function test_cms_content_seeder_fills_missing_keys_only(): void
    {
        Setting::query()->where('key', 'email')->update(['value' => 'kept@alwinco.ir']);
        Setting::query()->where('key', 'site_name')->delete();
        $this->assertNull(Setting::query()->where('key', 'site_name')->first());

        $this->seed(CmsContentSeeder::class);

        $restored = Setting::query()->where('key', 'site_name')->first();
        $this->assertNotNull($restored);
        $this->assertSame('ALWIN', $restored->value);
        $this->assertSame('kept@alwinco.ir', Setting::query()->where('key', 'email')->value('value'));
    }

    public function test_sql_dump_warns_against_live_import(): void
    {
        $sql = MysqlDumper::dump();
        $this->assertStringContainsString('Do NOT import this into a live database that already has data', $sql);
        $this->assertStringContainsString('DROP TABLE IF EXISTS', $sql);
    }

    public function test_backup_command_writes_sql_without_changing_data(): void
    {
        $productCount = Product::query()->count();
        $dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'alwin-backup-test-'.uniqid('', true);
        $this->artisan('db:backup', [
            '--dir' => $dir,
            '--name' => 'alwin_backup_before_migration',
        ])->assertSuccessful();

        $path = $dir.DIRECTORY_SEPARATOR.'alwin_backup_before_migration.sql';
        $this->assertFileExists($path);
        $this->assertStringContainsString('Do NOT import this into a live database that already has data', (string) file_get_contents($path));
        $this->assertSame($productCount, Product::query()->count());
        @unlink($path);
        @rmdir($dir);
    }

    public function test_destructive_commands_can_be_blocked(): void
    {
        $users = User::query()->count();
        DB::prohibitDestructiveCommands();
        try {
            $this->artisan('db:wipe')->assertFailed();
            $this->artisan('migrate:fresh')->assertFailed();
            $this->assertSame($users, User::query()->count());
        } finally {
            DB::prohibitDestructiveCommands(false);
        }
    }
}
