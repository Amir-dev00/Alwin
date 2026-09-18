<?php

namespace Tests\Feature;

use App\Models\ContactInquiry;
use App\Models\User;
use App\Support\MysqlDumper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemCheckTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_super_admin_can_open_system_check(): void
    {
        $admin = User::query()->where('role', User::ROLE_SUPER_ADMIN)->first();

        $this->actingAs($admin)
            ->get('/admin/system-check')
            ->assertOk()
            ->assertSee('سلامت استقرار')
            ->assertSee('ALWIN v');
    }

    public function test_editor_cannot_open_system_check(): void
    {
        $editor = User::factory()->create(['role' => User::ROLE_EDITOR]);
        $this->actingAs($editor)->get('/admin/system-check')->assertForbidden();
    }

    public function test_guest_is_redirected_from_system_check(): void
    {
        $this->get('/admin/system-check')->assertRedirect('/admin/login');
    }

    public function test_super_admin_can_run_pending_migrations(): void
    {
        $admin = User::query()->where('role', User::ROLE_SUPER_ADMIN)->first();

        $this->actingAs($admin)
            ->post('/admin/system-check/migrate')
            ->assertRedirect();
    }

    public function test_cron_requires_secret(): void
    {
        config(['alwin.cron_secret' => 'unit-test-cron-secret']);

        $this->get('/cron/wrong-token')->assertNotFound();
        $this->get('/cron/unit-test-cron-secret')->assertOk()->assertJsonPath('ok', true);
    }

    public function test_production_sql_keeps_schema_and_skips_operational_rows(): void
    {
        ContactInquiry::query()->create([
            'name' => 'نباید در دامپ باشد',
            'phone' => '09120000000',
            'subject' => 'callback',
            'source' => 'contact',
            'status' => ContactInquiry::STATUS_NEW,
        ]);

        $sql = MysqlDumper::dump();
        $this->assertStringContainsString('CREATE TABLE `contact_inquiries`', $sql);
        $this->assertStringNotContainsString('INSERT INTO `contact_inquiries`', $sql);
        $this->assertStringContainsString('INSERT INTO `users`', $sql);
        $this->assertStringContainsString('INSERT INTO `articles`', $sql);
    }
}
