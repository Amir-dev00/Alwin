<?php

namespace Tests\Feature;

use App\Models\PricingGlass;
use App\Models\PricingModel;
use App\Models\User;
use App\Services\PricingCalculator;
use App\Services\PricingCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_public_catalog_has_materials_windows_doors_and_screens(): void
    {
        $json = $this->getJson('/api/v1/pricing')->assertOk();
        $this->assertSame('2.1', $json->json('schema_version'));
        $models = collect($json->json('models'));
        $this->assertCount(42, $models);
        $screen = $models->firstWhere('id', 39) ?? $models['39'] ?? $models[39] ?? null;
        $this->assertIsArray($screen);
        $this->assertSame('screen', $screen['family']);
        $this->assertSame(850000, $screen['areaRate']);
        $this->assertSame('aluminum', $json->json('tabs.0.id'));
        $this->assertSame('آلومینیوم', $json->json('tabs.0.label'));
        $this->assertSame('upvc', $json->json('tabs.1.id'));
        $this->assertSame('یو پی وی سی (UPVC)', $json->json('tabs.1.label'));
        $this->assertSame(680000, $json->json('glass.0.pricePerSqm'));
        $wintech = collect($json->json('profiles'))->firstWhere('id', 'wintech');
        $this->assertSame('upvc', $wintech['material']);
        $this->assertSame(250250, $wintech['prices']['frame']);
        $aluminum = collect($json->json('profiles'))->firstWhere('id', 'al_thermal');
        $this->assertSame('aluminum', $aluminum['material']);
        $tilt = collect($json->json('hardwareTypes'))->firstWhere('id', 'tilt');
        $this->assertSame(500000, $tilt['prices']['turk']);
        $this->assertSame(1200000, $tilt['prices']['germany']);
        $families = collect($json->json('categories.aluminum'))->pluck('id')->all();
        $this->assertSame(['windows', 'doors', 'screens'], $families);
    }

    public function test_fixed_window_quote_matches_profile_plus_glass_formula(): void
    {
        $quote = PricingCalculator::quote([
            'model_id' => 1,
            'width_cm' => 120,
            'height_cm' => 150,
            'quantity' => 1,
            'profile_id' => 'wintech',
            'glass_id' => 'simple',
            'hardware_id' => 'turk',
        ]);

        $this->assertTrue($quote['ok']);
        $this->assertSame(0, $quote['hardware_total']);
        $this->assertEqualsWithDelta(1.6, $quote['glass_area'], 0.001);
        $this->assertSame(1088000, $quote['glass_total']);
        $this->assertSame(1518750, $quote['profile_total']);
        $this->assertSame(2607000, $quote['total']);
    }

    public function test_screen_quote_adds_area_rate_to_frame(): void
    {
        $quote = PricingCalculator::quote([
            'model_id' => 39,
            'width_cm' => 100,
            'height_cm' => 100,
            'quantity' => 1,
            'profile_id' => 'wintech',
            'glass_id' => 'simple',
            'hardware_id' => 'turk',
        ]);

        $this->assertTrue($quote['ok']);
        $this->assertSame(0, $quote['glass_total']);
        $this->assertSame(0, $quote['hardware_total']);
        $this->assertSame(850000, $quote['screen_total']);
        $this->assertSame(1001000, $quote['profile_total']);
        $this->assertSame(1851000, $quote['total']);
    }

    public function test_casement_tilt_turn_costs_more_than_tilt(): void
    {
        $base = [
            'model_id' => 2,
            'width_cm' => 120,
            'height_cm' => 150,
            'quantity' => 1,
            'profile_id' => 'wintech',
            'glass_id' => 'simple',
            'hardware_id' => 'turk',
        ];
        $tilt = PricingCalculator::quote($base + ['hardware_type' => 'tilt']);
        $turn = PricingCalculator::quote($base + ['hardware_type' => 'tilt_turn']);
        $this->assertSame(500000, $tilt['hardware_total']);
        $this->assertSame(1000000, $turn['hardware_total']);
        $this->assertGreaterThan($tilt['total'], $turn['total']);
    }

    public function test_service_door_uses_panel_and_skips_glass(): void
    {
        $quote = PricingCalculator::quote([
            'model_id' => 24,
            'width_cm' => 120,
            'height_cm' => 210,
            'quantity' => 1,
            'profile_id' => 'wintech',
            'glass_id' => 'simple',
            'hardware_id' => 'turk',
        ]);
        $this->assertTrue($quote['ok']);
        $this->assertSame(0, $quote['glass_total']);
        $this->assertSame(900000, $quote['hardware_total']);
        $panel = collect($quote['lines']['profile'])->firstWhere('key', 'panel');
        $this->assertNotNull($panel);
        $this->assertGreaterThan(0, $panel['total']);
    }

    public function test_estimate_api_and_lead_capture(): void
    {
        $this->postJson('/api/v1/calculator/estimate', [
            'model_id' => 1,
            'width_cm' => 120,
            'height_cm' => 150,
            'profile_id' => 'wintech',
            'glass_id' => 'simple',
            'hardware_id' => 'turk',
        ])->assertOk()->assertJsonPath('total', 2607000);

        $this->postJson('/api/v1/leads', [
            'name' => 'علی تست',
            'phone' => '09120000000',
            'model_id' => 1,
            'width_cm' => 120,
            'height_cm' => 150,
            'quantity' => 1,
            'notes' => json_encode([
                'profile' => 'wintech',
                'glass' => 'simple',
                'hardware' => 'turk',
            ]),
        ])->assertCreated()->assertJsonPath('ok', true);
    }

    public function test_admin_can_change_glass_price_and_catalog_updates(): void
    {
        $admin = User::query()->where('role', User::ROLE_SUPER_ADMIN)->first();
        $glass = PricingGlass::query()->where('key', 'simple')->first();

        $this->actingAs($admin)->put('/admin/pricing/glasses', [
            'glasses' => [
                $glass->id => [
                    'name' => $glass->name,
                    'price_per_sqm' => 700000,
                    'is_active' => '1',
                ],
            ],
        ])->assertRedirect();

        $this->getJson('/api/v1/pricing')->assertJsonPath('glass.0.pricePerSqm', 700000);
    }

    public function test_editor_can_open_pricing_studio(): void
    {
        $editor = User::factory()->create(['role' => User::ROLE_EDITOR]);
        $this->actingAs($editor)->get('/admin/pricing')->assertOk()->assertSee('موتور قیمت');
        $this->assertNotNull(PricingModel::query()->where('catalog_id', 37)->first());
    }
}
