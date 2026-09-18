<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\PricingBrand;
use App\Models\PricingBrandPrice;
use App\Models\PricingComponent;
use App\Models\PricingGlass;
use App\Models\PricingHardware;
use App\Models\PricingLead;
use App\Models\PricingModel;
use App\Models\PricingSetting;
use App\Support\Audit;
use App\Services\PricingBom;
use App\Services\PricingCalculator;
use App\Services\PricingCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PricingController extends Controller
{
    public function index(Request $request): View
    {
        $this->guard();
        $tab = $request->string('tab')->toString() ?: 'studio';
        if (! in_array($tab, ['studio', 'models', 'profiles', 'glass', 'hardware', 'settings', 'leads'], true)) {
            $tab = 'studio';
        }

        return view('admin.pricing.index', [
            'tab' => $tab,
            'brands' => PricingBrand::query()->with('prices')->orderBy('sort_order')->get(),
            'components' => PricingComponent::query()->orderBy('sort_order')->get(),
            'glasses' => PricingGlass::query()->orderBy('sort_order')->get(),
            'hardwares' => PricingHardware::query()->orderBy('sort_order')->get(),
            'models' => PricingModel::query()->orderBy('sort_order')->get(),
            'settings' => PricingSetting::query()->orderBy('id')->get()->keyBy('key'),
            'families' => PricingBom::families(),
            'leads' => PricingLead::query()->latest()->limit(80)->get(),
            'leadCount' => PricingLead::query()->where('status', 'new')->count(),
            'catalog' => PricingCatalog::build(),
        ]);
    }

    public function preview(Request $request): JsonResponse
    {
        $this->guard();
        $data = $request->validate([
            'model_id' => ['required', 'integer'],
            'width_cm' => ['required', 'numeric', 'min:1', 'max:800'],
            'height_cm' => ['required', 'numeric', 'min:1', 'max:800'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
            'profile_id' => ['nullable', 'string', 'max:32'],
            'glass_id' => ['nullable', 'string', 'max:32'],
            'hardware_id' => ['nullable', 'string', 'max:32'],
            'hardware_type' => ['nullable', 'string', 'max:32'],
        ]);

        return response()->json(PricingCalculator::quote($data));
    }

    public function updateBrands(Request $request): RedirectResponse
    {
        $this->guard();
        $payload = $request->validate([
            'prices' => ['required', 'array'],
            'prices.*' => ['array'],
        ]);
        foreach ($payload['prices'] as $brandId => $components) {
            $brand = PricingBrand::query()->find($brandId);
            if (! $brand) {
                continue;
            }
            foreach ($components as $key => $value) {
                $price = $this->nullableMoney($value);
                PricingBrandPrice::query()->updateOrCreate(
                    ['brand_id' => $brand->id, 'component_key' => $key],
                    ['price' => $price]
                );
            }
        }
        Audit::log('update', null, 'ویرایش نرخ پروفیل');
        PricingCatalog::flush();

        return redirect()->route('admin.pricing.index', ['tab' => 'profiles'])->with('success', 'نرخ پروفیل‌ها ذخیره شد.');
    }

    public function updateGlasses(Request $request): RedirectResponse
    {
        $this->guard();
        $payload = $request->validate([
            'glasses' => ['required', 'array'],
            'glasses.*.name' => ['required', 'string', 'max:190'],
            'glasses.*.price_per_sqm' => ['required', 'numeric', 'min:0'],
            'glasses.*.is_active' => ['nullable'],
        ]);
        foreach ($payload['glasses'] as $id => $row) {
            $item = PricingGlass::query()->find($id);
            if (! $item) {
                continue;
            }
            $item->name = $row['name'];
            $item->price_per_sqm = (int) $row['price_per_sqm'];
            $item->is_active = $request->boolean("glasses.$id.is_active");
            $item->save();
        }
        Audit::log('update', null, 'ویرایش نرخ شیشه');
        PricingCatalog::flush();

        return redirect()->route('admin.pricing.index', ['tab' => 'glass'])->with('success', 'نرخ شیشه ذخیره شد.');
    }

    public function storeGlass(Request $request): RedirectResponse
    {
        $this->guard();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'price_per_sqm' => ['required', 'numeric', 'min:0'],
        ]);
        $key = 'g'.substr(md5($data['name'].microtime()), 0, 8);
        PricingGlass::query()->create([
            'key' => $key,
            'name' => $data['name'],
            'price_per_sqm' => (int) $data['price_per_sqm'],
            'sort_order' => (int) PricingGlass::query()->max('sort_order') + 1,
            'is_active' => true,
        ]);
        Audit::log('create', null, 'افزودن نوع شیشه '.$data['name']);
        PricingCatalog::flush();

        return redirect()->route('admin.pricing.index', ['tab' => 'glass'])->with('success', 'نوع شیشه افزوده شد.');
    }

    public function updateHardwares(Request $request): RedirectResponse
    {
        $this->guard();
        $payload = $request->validate([
            'hardwares' => ['required', 'array'],
            'hardwares.*.name' => ['required', 'string', 'max:190'],
            'hardwares.*.price_turk' => ['required', 'numeric', 'min:0'],
            'hardwares.*.price_germany' => ['required', 'numeric', 'min:0'],
        ]);
        foreach ($payload['hardwares'] as $id => $row) {
            $item = PricingHardware::query()->find($id);
            if (! $item) {
                continue;
            }
            $item->name = $row['name'];
            $item->price_turk = (int) $row['price_turk'];
            $item->price_germany = (int) $row['price_germany'];
            $item->is_active = $request->boolean("hardwares.$id.is_active");
            $item->save();
        }
        Audit::log('update', null, 'ویرایش نرخ یراق');
        PricingCatalog::flush();

        return redirect()->route('admin.pricing.index', ['tab' => 'hardware'])->with('success', 'نرخ یراق ذخیره شد.');
    }

    public function storeHardware(Request $request): RedirectResponse
    {
        $this->guard();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'price_turk' => ['required', 'numeric', 'min:0'],
            'price_germany' => ['required', 'numeric', 'min:0'],
        ]);
        PricingHardware::query()->create([
            'key' => 'h'.substr(md5($data['name'].microtime()), 0, 8),
            'name' => $data['name'],
            'price_turk' => (int) $data['price_turk'],
            'price_germany' => (int) $data['price_germany'],
            'sort_order' => (int) PricingHardware::query()->max('sort_order') + 1,
            'is_active' => true,
        ]);
        Audit::log('create', null, 'افزودن نوع یراق '.$data['name']);
        PricingCatalog::flush();

        return redirect()->route('admin.pricing.index', ['tab' => 'hardware'])->with('success', 'نوع یراق افزوده شد.');
    }

    public function updateModels(Request $request): RedirectResponse
    {
        $this->guard();
        $payload = $request->validate([
            'models' => ['required', 'array'],
        ]);
        foreach ($payload['models'] as $id => $row) {
            $item = PricingModel::query()->find($id);
            if (! $item) {
                continue;
            }
            $item->glass_deduction = $row['glass_deduction'] === '' || $row['glass_deduction'] === null
                ? null
                : (float) $row['glass_deduction'];
            $item->hardware_qty = (int) ($row['hardware_qty'] ?? 0);
            $item->hardware_mode = in_array($row['hardware_mode'] ?? '', ['none', 'casement', 'fixed'], true)
                ? $row['hardware_mode']
                : $item->hardware_mode;
            $item->hardware_type_key = $row['hardware_type_key'] ?? $item->hardware_type_key;
            $item->lites = max(1, (int) ($row['lites'] ?? 1));
            $item->sashes = max(0, (int) ($row['sashes'] ?? 0));
            $item->transom_top = ! empty($row['transom_top']);
            $item->transom_bottom = ! empty($row['transom_bottom']);
            $item->panel_ratio = (float) ($row['panel_ratio'] ?? 0);
            $item->area_rate = $row['area_rate'] === '' || $row['area_rate'] === null
                ? null
                : (int) $row['area_rate'];
            $item->is_active = ! empty($row['is_active']);
            $item->family = $row['family'] ?? $item->family;

            if (! empty($row['recipe']) && is_array($row['recipe'])) {
                $recipe = [];
                foreach ($row['recipe'] as $key => $coeffs) {
                    $recipe[$key] = [
                        'w' => (float) ($coeffs['w'] ?? 0),
                        'h' => (float) ($coeffs['h'] ?? 0),
                        'c' => (float) ($coeffs['c'] ?? 0),
                        'area' => (float) ($coeffs['area'] ?? 0),
                    ];
                }
                $item->recipe = $recipe;
                $item->recipe_locked = true;
            } elseif (! $item->recipe_locked) {
                $item->recipe = PricingBom::build($item->toArray());
            }
            $item->save();
        }
        Audit::log('update', null, 'ویرایش مدل‌های ماشین‌حساب');
        PricingCatalog::flush();

        return redirect()->route('admin.pricing.index', ['tab' => 'models'])->with('success', 'مدل‌ها ذخیره شد.');
    }

    public function rebuildRecipe(PricingModel $model): RedirectResponse
    {
        $this->guard();
        $model->recipe = PricingBom::build($model->toArray());
        $model->recipe_locked = false;
        $model->save();
        Audit::log('update', $model, 'بازنشانی مصرف پروفیل '.$model->name);
        PricingCatalog::flush();

        return redirect()->route('admin.pricing.index', ['tab' => 'models'])->with('success', 'مصرف پروفیل «'.$model->name.'» از روی ساختار دوباره ساخته شد.');
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $this->guard();
        $data = $request->validate([
            'waste_percent' => ['required', 'numeric', 'min:0', 'max:40'],
            'round_to' => ['required', 'integer', 'min:1'],
            'fallback_brand' => ['required', 'string', 'max:32'],
        ]);
        foreach ($data as $key => $value) {
            PricingSetting::query()->where('key', $key)->update(['value' => (string) $value]);
        }
        Audit::log('update', null, 'ویرایش تنظیمات ماشین‌حساب');
        PricingCatalog::flush();

        return redirect()->route('admin.pricing.index', ['tab' => 'settings'])->with('success', 'تنظیمات ماشین‌حساب ذخیره شد.');
    }

    public function markLead(PricingLead $lead): RedirectResponse
    {
        $this->guard();
        $lead->status = $lead->status === 'done' ? 'new' : 'done';
        $lead->save();

        return redirect()->route('admin.pricing.index', ['tab' => 'leads'])->with('success', 'وضعیت استعلام به‌روز شد.');
    }

    private function guard(): void
    {
        abort_unless(auth()->user()?->isEditor(), 403);
    }

    private function nullableMoney(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
