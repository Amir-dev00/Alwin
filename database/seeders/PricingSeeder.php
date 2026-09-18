<?php

namespace Database\Seeders;

use App\Models\PricingBrand;
use App\Models\PricingBrandPrice;
use App\Models\PricingComponent;
use App\Models\PricingGlass;
use App\Models\PricingHardware;
use App\Models\PricingModel;
use App\Models\PricingSetting;
use App\Services\PricingBom;
use App\Services\PricingCatalog;
use App\Support\SafeSeed;
use Illuminate\Database\Seeder;

class PricingSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedComponents();
        $this->seedBrands();
        $this->seedGlasses();
        $this->seedHardwares();
        $this->seedSettings();
        $this->seedModels();
        PricingCatalog::flush();
    }

    private function seedComponents(): void
    {
        $rows = [
            ['frame', 'فریم', 'meter'],
            ['sash', 'لنگه پنجره', 'meter'],
            ['mullion', 'وادار', 'meter'],
            ['zehvar', 'زهوار', 'meter'],
            ['door_sash', 'لنگه درب', 'meter'],
            ['frame_keshoee', 'فریم کشویی', 'meter'],
            ['sash_keshoee', 'لنگه کشویی', 'meter'],
            ['frame_keshoee_takreil', 'فریم کشویی تک‌ریل', 'meter'],
            ['conector_sash', 'اتصال لنگه', 'meter'],
            ['mullion_katibe', 'وادار کتیبه', 'meter'],
            ['interlock', 'اینترلاک', 'meter'],
            ['overhong', 'اورهنگ', 'meter'],
            ['darpoosh', 'درپوش', 'meter'],
            ['panel', 'پنل', 'sqm'],
        ];
        foreach ($rows as $i => [$key, $name, $unit]) {
            SafeSeed::missing(PricingComponent::class, ['key' => $key], [
                'name' => $name,
                'unit' => $unit,
                'sort_order' => $i,
            ]);
        }
    }

    private function seedBrands(): void
    {
        $brands = [
            ['vistabest', 'ویستابست', 'upvc'],
            ['wintech', 'وینتک', 'upvc'],
            ['pluspan', 'پلاس پن', 'upvc'],
            ['winplus', 'وین پلاس', 'upvc'],
            ['al_normal', 'آلومینیوم نرمال', 'aluminum'],
            ['al_thermal', 'ترمال بریک', 'aluminum'],
        ];
        foreach ($brands as $i => [$key, $name, $material]) {
            SafeSeed::missing(PricingBrand::class, ['key' => $key], [
                'name' => $name,
                'material' => $material,
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }

        $matrix = [
            'frame' => [305000, 250250, 200000, 270000],
            'sash' => [337000, 279000, 229000, 300000],
            'mullion' => [339000, 279000, 227000, 300000],
            'zehvar' => [35000, 31000, 31000, 33000],
            'door_sash' => [425000, 356000, 296000, 386000],
            'frame_keshoee' => [330000, 314000, null, 337000],
            'sash_keshoee' => [310000, 316000, null, 338000],
            'frame_keshoee_takreil' => [null, 448000, null, 483000],
            'conector_sash' => [null, 385000, null, 385000],
            'mullion_katibe' => [null, 627000, null, 685000],
            'interlock' => [46000, 50000, null, 60000],
            'overhong' => [288000, 256000, 256000, 276000],
            'darpoosh' => [null, 72000, null, 76000],
            'panel' => [1353000, 950000, 950000, 995000],
        ];

        $brandIds = PricingBrand::query()->orderBy('sort_order')->pluck('id', 'key');
        $keys = ['vistabest', 'wintech', 'pluspan', 'winplus'];
        foreach ($matrix as $component => $prices) {
            foreach ($keys as $i => $brandKey) {
                SafeSeed::missing(PricingBrandPrice::class, [
                    'brand_id' => $brandIds[$brandKey],
                    'component_key' => $component,
                ], ['price' => $prices[$i]]);
            }
        }

        $this->seedAluminumFrom('wintech', 'al_normal', 1.28);
        $this->seedAluminumFrom('vistabest', 'al_thermal', 1.18);
    }

    private function seedAluminumFrom(string $sourceKey, string $targetKey, float $factor): void
    {
        $source = PricingBrand::query()->where('key', $sourceKey)->first();
        $target = PricingBrand::query()->where('key', $targetKey)->first();
        if (! $source || ! $target) {
            return;
        }
        foreach (PricingBrandPrice::query()->where('brand_id', $source->id)->get() as $row) {
            $price = $row->price === null ? null : (int) (round(($row->price * $factor) / 1000) * 1000);
            SafeSeed::missing(PricingBrandPrice::class, [
                'brand_id' => $target->id,
                'component_key' => $row->component_key,
            ], ['price' => $price]);
        }
    }

    private function seedGlasses(): void
    {
        $rows = [
            ['simple', 'شیشه دوجداره فلوت ساده', 680000],
            ['gold', 'شیشه دوجداره رفلکس طلایی', 780000],
            ['silver', 'شیشه دوجداره رفلکس نقره‌ای', 880000],
            ['bronze', 'شیشه دوجداره رفلکس برنز', 980000],
            ['smoke', 'شیشه دوجداره رفلکس دودی', 1250000],
        ];
        foreach ($rows as $i => [$key, $name, $price]) {
            SafeSeed::missing(PricingGlass::class, ['key' => $key], [
                'name' => $name,
                'price_per_sqm' => $price,
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }
    }

    private function seedHardwares(): void
    {
        $rows = [
            ['none', 'بدون یراق', 0, 0],
            ['tilt', 'تک حالته', 500000, 1200000],
            ['tilt_turn', 'دوحالته', 1000000, 2200000],
            ['balcony', 'بالکنی', 1200000, 5000000],
            ['service', 'سرویسی', 900000, 3000000],
            ['sliding', 'کشویی', 800000, 2300000],
            ['french_door', 'درب فرانسوی', 1700000, 6000000],
            ['french_window', 'پنجره فرانسوی', 1700000, 3500000],
            ['accordion3', 'درب آکاردئونی ۳ لنگه', 30000000, 45000000],
            ['accordion4', 'درب آکاردئونی ۴ لنگه', 35000000, 50000000],
            ['accordion5', 'درب آکاردئونی ۵ لنگه', 40000000, 60000000],
            ['volkswagen', 'درب فولکس واگنی', 12000000, 20000000],
        ];
        foreach ($rows as $i => [$key, $name, $turk, $ger]) {
            SafeSeed::missing(PricingHardware::class, ['key' => $key], [
                'name' => $name,
                'price_turk' => $turk,
                'price_germany' => $ger,
                'sort_order' => $i,
                'is_active' => true,
            ]);
        }
    }

    private function seedSettings(): void
    {
        $rows = [
            ['waste_percent', 'درصد پرت برش پروفیل', '0'],
            ['round_to', 'گرد کردن قیمت نهایی (تومان)', '1000'],
            ['fallback_brand', 'برند جایگزین برای نرخ خالی', 'wintech'],
        ];
        foreach ($rows as [$key, $label, $value]) {
            SafeSeed::missing(PricingSetting::class, ['key' => $key], [
                'label' => $label,
                'value' => $value,
            ]);
        }
    }

    private function seedModels(): void
    {
        $models = [
            [1, 'پنجره ثابت بدون بازشو', 'windows', 'fixed', 'casement', 0.2, 0, 'none', 'none', 1, 0, 0, 0, 0],
            [2, 'پنجره تک لت بازشو', 'windows', 'casement', 'casement', 0.5, 1, 'casement', null, 1, 1, 0, 0, 0],
            [3, 'پنجره تک لت بازشو با کتیبه بالا', 'windows', 'casement', 'casement', 0.2, 1, 'casement', null, 1, 1, 1, 0, 0],
            [4, 'پنجره تک لت بازشو با کتیبه پایین', 'windows', 'casement', 'casement', 0.2, 1, 'casement', null, 1, 1, 0, 1, 0],
            [5, 'پنجره تک لت بازشو با کتیبه بالا و پایین', 'windows', 'casement', 'casement', 0.0, 1, 'casement', null, 1, 1, 1, 1, 0],
            [6, 'پنجره دو لت با یک بازشو', 'windows', 'casement', 'casement', 0.5, 1, 'casement', null, 2, 1, 0, 0, 0],
            [7, 'پنجره دو لت با کتیبه بالا', 'windows', 'casement', 'casement', 0.5, 1, 'casement', null, 2, 1, 1, 0, 0],
            [8, 'پنجره دو لت با کتیبه پایین', 'windows', 'casement', 'casement', 0.5, 1, 'casement', null, 2, 1, 0, 1, 0],
            [9, 'پنجره دو لت با کتیبه بالا و پایین', 'windows', 'casement', 'casement', 0.5, 1, 'casement', null, 2, 1, 1, 1, 0],
            [10, 'پنجره سه لت با بازشو وسط', 'windows', 'casement', 'casement', 0.5, 1, 'casement', null, 3, 1, 0, 0, 0],
            [11, 'پنجره سه لت با بازشو وسط و کتیبه بالا', 'windows', 'casement', 'casement', 0.5, 1, 'casement', null, 3, 1, 1, 0, 0],
            [12, 'پنجره سه لت با بازشو وسط و کتیبه پایین', 'windows', 'casement', 'casement', 0.5, 1, 'casement', null, 3, 1, 0, 1, 0],
            [13, 'پنجره سه لت با بازشو وسط و کتیبه بالا و پایین', 'windows', 'casement', 'casement', 1.0, 1, 'casement', null, 3, 1, 1, 1, 0],
            [14, 'پنجره سه لت با دو بازشو کنار', 'windows', 'casement', 'casement', 0.8, 2, 'casement', null, 3, 2, 0, 0, 0],
            [15, 'پنجره سه لت با دو بازشو کنار و کتیبه پایین', 'windows', 'casement', 'casement', 0.8, 2, 'casement', null, 3, 2, 0, 1, 0],
            [16, 'پنجره سه لت با دو بازشو کنار و کتیبه بالا', 'windows', 'casement', 'casement', 0.8, 2, 'casement', null, 3, 2, 1, 0, 0],
            [17, 'پنجره سه لت با دو بازشو کنار و کتیبه بالا و پایین', 'windows', 'casement', 'casement', 0.5, 2, 'casement', null, 3, 2, 1, 1, 0],
            [18, 'پنجره دو لت با بازشو کنار', 'windows', 'casement', 'casement', 0.5, 1, 'casement', null, 2, 1, 0, 0, 0],
            [19, 'پنجره دو لت با بازشو کنار و کتیبه بالا', 'windows', 'casement', 'casement', 0.5, 1, 'casement', null, 2, 1, 1, 0, 0],
            [20, 'پنجره دو لت با بازشو کنار و کتیبه پایین', 'windows', 'casement', 'casement', 0.5, 1, 'casement', null, 2, 1, 0, 1, 0],
            [21, 'پنجره دو لت با بازشو کنار و کتیبه بالا و پایین', 'windows', 'casement', 'casement', 0.0, 1, 'casement', null, 2, 1, 1, 1, 0],
            [22, 'درب سوییچی تمام شیشه', 'doors', 'switch', 'door', 0.6, 1, 'fixed', 'balcony', 1, 1, 0, 0, 0],
            [23, 'درب سوییچی شیشه و پنل', 'doors', 'switch', 'door', 1.0, 1, 'fixed', 'balcony', 1, 1, 0, 0, 0.4],
            [24, 'درب سرویسی تمام پنل', 'doors', 'switch', 'door', null, 1, 'fixed', 'service', 1, 1, 0, 0, 1],
            [25, 'درب سوییچی دو لت تمام شیشه', 'doors', 'switch', 'door', 0.9, 1, 'fixed', 'balcony', 2, 1, 0, 0, 0],
            [26, 'درب سوییچی دو لت شیشه و پنل', 'doors', 'switch', 'door', 1.5, 1, 'fixed', 'balcony', 2, 1, 0, 0, 0.35],
            [27, 'درب سوییچی سه لت وسط بازشو', 'doors', 'switch', 'door', 1.0, 1, 'fixed', 'balcony', 3, 1, 0, 0, 0],
            [28, 'درب سوییچی دو لنگه بازشو (فرانسوی)', 'doors', 'switch', 'door', 1.0, 2, 'fixed', 'french_door', 2, 2, 0, 0, 0],
            [29, 'پنجره کشویی دو لنگه', 'windows', 'sliding', 'sliding', 0.8, 1, 'fixed', 'sliding', 2, 2, 0, 0, 0],
            [30, 'پنجره کشویی با کتیبه بالا', 'windows', 'sliding', 'sliding', 0.8, 1, 'fixed', 'sliding', 2, 2, 1, 0, 0],
            [31, 'پنجره کشویی سه لت وسط بازشو', 'windows', 'sliding', 'sliding', 0.7, 1, 'fixed', 'sliding', 3, 2, 0, 0, 0],
            [32, 'پنجره کشویی سه لت وسط بازشو با کتیبه بالا', 'windows', 'sliding', 'sliding', 0.8, 1, 'fixed', 'sliding', 3, 2, 1, 0, 0],
            [33, 'پنجره کشویی چهار لت وسط بازشو (فرانسوی)', 'windows', 'sliding', 'sliding', 1.2, 2, 'fixed', 'sliding', 4, 2, 0, 0, 0],
            [34, 'پنجره فرانسوی', 'windows', 'french', 'casement', 0.6, 1, 'fixed', 'french_window', 2, 2, 0, 0, 0],
            [35, 'درب آکاردئونی سه لنگه', 'doors', 'accordion', 'accordion', 1.5, 1, 'fixed', 'accordion3', 3, 3, 0, 0, 0],
            [36, 'درب آکاردئونی چهار لنگه', 'doors', 'accordion', 'accordion', 2.0, 1, 'fixed', 'accordion4', 4, 4, 0, 0, 0],
            [37, 'درب آکاردئونی پنج لنگه', 'doors', 'accordion', 'accordion', 2.6, 1, 'fixed', 'accordion5', 5, 5, 0, 0, 0],
            [38, 'درب فولکس واگنی', 'doors', 'special', 'volkswagen', 0.7, 1, 'fixed', 'volkswagen', 2, 2, 0, 0, 0],
        ];

        foreach ($models as $row) {
            [$id, $name, $tab, $cat, $family, $ded, $hwQty, $hwMode, $hwType, $lites, $sashes, $top, $bot, $panel] = $row;
            $struct = [
                'lites' => $lites,
                'sashes' => $sashes,
                'transom_top' => (bool) $top,
                'transom_bottom' => (bool) $bot,
                'family' => $family,
                'panel_ratio' => $panel,
            ];
            SafeSeed::missing(PricingModel::class, ['catalog_id' => $id], [
                'name' => $name,
                'tab' => $tab,
                'category_key' => $cat,
                'family' => $family,
                'glass_deduction' => $ded,
                'hardware_qty' => $hwQty,
                'hardware_mode' => $hwMode,
                'hardware_type_key' => $hwType,
                'lites' => $lites,
                'sashes' => $sashes,
                'transom_top' => (bool) $top,
                'transom_bottom' => (bool) $bot,
                'panel_ratio' => $panel,
                'recipe' => PricingBom::build($struct),
                'sort_order' => $id,
                'is_active' => true,
            ]);
        }

        $this->seedScreens();
    }

    private function seedScreens(): void
    {
        $screens = [
            [39, 'توری پلیسه‌ای پنجره', 850000, 'assets/Images/Lace/1/close.webp'],
            [40, 'توری آکاردئونی افقی دو طرفه (پلیسه‌ای)', 950000, 'assets/Images/Lace/2/close.webp'],
            [41, 'توری کشویی افقی یک طرفه (پلیسه‌ای)', 800000, 'assets/Images/Lace/3/close.webp'],
            [42, 'توری رولینگ عمودی پنجره', 1100000, 'assets/Images/Lace/4/close.webp'],
        ];
        foreach ($screens as $row) {
            [$id, $name, $rate, $image] = $row;
            $struct = [
                'lites' => 1,
                'sashes' => 0,
                'transom_top' => false,
                'transom_bottom' => false,
                'family' => 'screen',
                'panel_ratio' => 0,
            ];
            SafeSeed::missing(PricingModel::class, ['catalog_id' => $id], [
                'name' => $name,
                'tab' => 'screens',
                'category_key' => 'screens',
                'family' => 'screen',
                'glass_deduction' => null,
                'hardware_qty' => 0,
                'hardware_mode' => 'none',
                'hardware_type_key' => 'none',
                'lites' => 1,
                'sashes' => 0,
                'transom_top' => false,
                'transom_bottom' => false,
                'panel_ratio' => 0,
                'area_rate' => $rate,
                'image' => $image,
                'recipe' => PricingBom::build($struct),
                'sort_order' => $id,
                'is_active' => true,
            ]);
        }
    }
}
