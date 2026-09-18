<?php

namespace App\Services;

use App\Models\PricingBrand;
use App\Models\PricingComponent;
use App\Models\PricingGlass;
use App\Models\PricingHardware;
use App\Models\PricingModel;
use App\Models\PricingSetting;
use App\Support\SiteCache;
use Illuminate\Support\Facades\Cache;

final class PricingCatalog
{
    public const CACHE_KEY = 'api.pricing';

    public static function payload(): array
    {
        return Cache::remember(self::CACHE_KEY, 30, fn () => self::build());
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public static function build(): array
    {
        $components = PricingComponent::query()->orderBy('sort_order')->get()->map(fn ($c) => [
            'key' => $c->key,
            'name' => $c->name,
            'unit' => $c->unit,
        ])->values()->all();

        $brands = PricingBrand::query()->with('prices')->orderBy('sort_order')->get();
        $profiles = $brands->where('is_active', true)->map(function (PricingBrand $b) {
            $prices = [];
            foreach ($b->prices as $row) {
                $prices[$row->component_key] = $row->price;
            }

            return [
                'id' => $b->key,
                'name' => $b->name,
                'material' => $b->material ?: 'upvc',
                'prices' => $prices,
            ];
        })->values()->all();

        $glass = PricingGlass::query()->where('is_active', true)->orderBy('sort_order')->get()->map(fn ($g) => [
            'id' => $g->key,
            'name' => $g->name,
            'pricePerSqm' => (int) $g->price_per_sqm,
        ])->values()->all();

        $hardwareTypes = PricingHardware::query()->where('is_active', true)->orderBy('sort_order')->get()->map(fn ($h) => [
            'id' => $h->key,
            'name' => $h->name,
            'prices' => [
                'turk' => (int) $h->price_turk,
                'germany' => (int) $h->price_germany,
            ],
        ])->values()->all();

        $models = [];
        $families = [
            'windows' => [],
            'doors' => [],
            'screens' => [],
        ];
        $filters = [
            'windows' => [
                'fixed' => 'ثابت',
                'casement' => 'لولایی',
                'sliding' => 'کشویی',
                'french' => 'فرانسوی',
            ],
            'doors' => [
                'switch' => 'سوییچی',
                'accordion' => 'آکاردئونی',
                'special' => 'سایر',
            ],
            'screens' => [
                'screens' => 'توری',
            ],
        ];
        $familyLabels = [
            'windows' => 'پنجره‌ها',
            'doors' => 'درها',
            'screens' => 'توری',
        ];
        $familyHints = [
            'windows' => 'لولایی، کشویی، ثابت و فرانسوی',
            'doors' => 'سوییچی، آکاردئونی و فولکس',
            'screens' => 'پلیسه‌ای، آکاردئونی و رولینگ',
        ];

        foreach (PricingModel::query()->where('is_active', true)->orderBy('sort_order')->get() as $m) {
            $family = $m->tab === 'doors' ? 'doors' : ($m->tab === 'screens' ? 'screens' : 'windows');
            $models[$m->catalog_id] = [
                'id' => $m->catalog_id,
                'name' => $m->name,
                'tab' => $family,
                'categoryId' => $m->category_key,
                'family' => $m->family,
                'glassDeduction' => $m->glass_deduction,
                'hardwareQty' => $m->hardware_qty,
                'hardwareMode' => $m->hardware_mode,
                'hardwareType' => $m->hardware_type_key,
                'panelRatio' => (float) $m->panel_ratio,
                'areaRate' => (int) ($m->area_rate ?? 0),
                'image' => $m->image,
                'lites' => $m->lites,
                'sashes' => $m->sashes,
                'transomTop' => $m->transom_top,
                'transomBottom' => $m->transom_bottom,
                'recipe' => $m->recipe ?: [],
            ];
            $families[$family][] = $m->catalog_id;
        }

        $categoryList = [];
        foreach ($familyLabels as $id => $label) {
            $ids = $families[$id] ?? [];
            if (! $ids) {
                continue;
            }
            $chips = [];
            foreach ($filters[$id] ?? [] as $fid => $flabel) {
                $chipIds = [];
                foreach ($ids as $mid) {
                    if (($models[$mid]['categoryId'] ?? '') === $fid) {
                        $chipIds[] = $mid;
                    }
                }
                if ($chipIds) {
                    $chips[] = ['id' => $fid, 'label' => $flabel];
                }
            }
            $categoryList[] = [
                'id' => $id,
                'label' => $label,
                'hint' => $familyHints[$id] ?? '',
                'modelIds' => $ids,
                'filters' => $chips,
            ];
        }

        $tabs = [
            ['id' => 'aluminum', 'label' => 'آلومینیوم', 'hint' => 'نرمال و ترمال بریک'],
            ['id' => 'upvc', 'label' => 'یو پی وی سی (UPVC)', 'hint' => 'وینتک، ویستابست، پلاس‌پن و وین‌پلاس'],
        ];
        $catOut = [];
        foreach ($tabs as $tab) {
            $catOut[$tab['id']] = $categoryList;
        }

        $settings = PricingSetting::map();

        return [
            'schema_version' => '2.1',
            'settings' => [
                'waste_percent' => (float) ($settings['waste_percent'] ?? 0),
                'round_to' => (int) ($settings['round_to'] ?? 1000),
                'fallback_brand' => $settings['fallback_brand'] ?? 'wintech',
            ],
            'components' => $components,
            'profiles' => $profiles,
            'glass' => $glass,
            'hardware' => [
                ['id' => 'turk', 'name' => 'ترک'],
                ['id' => 'germany', 'name' => 'آلمانی'],
            ],
            'hardwareTypes' => $hardwareTypes,
            'tabs' => $tabs,
            'categories' => $catOut,
            'models' => $models,
        ];
    }

    public static function exportJs(?string $path = null): void
    {
        if (app()->environment('testing')) {
            return;
        }
        $path ??= public_path('assets'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'pricing-data.js');
        self::writeJs($path);
    }

    private static function writeJs(string $path): void
    {
        if (! is_dir(dirname($path))) {
            return;
        }
        $json = json_encode(self::build(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        $js = <<<JS
/**
 * Pricing catalog — generated from the CMS pricing studio.
 * Images: assets/img/pricing/{id}.webp
 */
window.ALWIN_PRICING = {$json};
window.ALWIN_PRICING.modelImage = function (id) {
  var m = window.ALWIN_PRICING.models && (window.ALWIN_PRICING.models[id] || window.ALWIN_PRICING.models[String(id)]);
  if (m && m.image) return m.image;
  return "assets/img/pricing/" + id + ".webp";
};

JS;
        file_put_contents($path, $js);
    }

    public static function flush(): void
    {
        self::forget();
        SiteCache::flush();
        self::exportJs();
    }
}
