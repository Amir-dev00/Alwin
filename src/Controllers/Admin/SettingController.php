<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Exceptions\ImageException;
use App\Models\Setting;
use App\Services\ImageService;
use App\Support\Audit;
use App\Support\SiteCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(private ImageService $images)
    {
    }

    public const SCREENS = [
        'general' => ['contact', 'brand', 'social', 'footer', 'video'],
        'seo' => ['seo'],
        'calculator' => ['calculator'],
    ];

    public function edit(Request $request): View
    {
        $screen = (string) $request->query('screen', 'general');
        if (! isset(self::SCREENS[$screen])) {
            $screen = 'general';
        }
        $groupKeys = self::SCREENS[$screen];
        $order = array_flip($groupKeys);
        $groups = Setting::query()
            ->whereIn('group', $groupKeys)
            ->orderBy('id')
            ->get()
            ->groupBy('group')
            ->sortBy(fn ($rows, $group) => $order[$group] ?? 99);

        return view('admin.settings.edit', [
            'groups' => $groups,
            'screen' => $screen,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $values = $request->validate([
            'settings' => ['required', 'array'],
            'setting_files' => ['nullable', 'array'],
            'setting_files.*' => ['nullable', 'file', 'max:'.(int) config('image.max_kilobytes', 51200)],
            'setting_clear' => ['nullable', 'array'],
        ]);
        $byKey = [];
        foreach ($values['settings'] as $id => $value) {
            $row = Setting::query()->find($id);
            if (! $row) {
                continue;
            }
            if ($request->boolean('setting_clear.'.$id)) {
                $row->value = '';
                $row->save();
                $byKey[$row->key] = $row;
                continue;
            }
            $file = $request->file('setting_files.'.$id);
            if ($file) {
                try {
                    $stored = $this->images->replace($file, 'settings', $row->value);
                    $row->value = $stored['path'];
                } catch (ImageException $e) {
                    return back()->withErrors(['settings.'.$id => $e->getMessage()]);
                }
            } else {
                $row->value = is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value;
            }
            $row->save();
            $byKey[$row->key] = $row;
        }
        if (isset($byKey['phone'], $byKey['phone_display']) && trim((string) $byKey['phone_display']->value) === '') {
            $byKey['phone_display']->value = $byKey['phone']->value;
            $byKey['phone_display']->save();
        }
        Audit::log('update', null, 'ویرایش تنظیمات سایت');
        SiteCache::flush();
        $screen = (string) $request->query('screen', 'general');
        if (! isset(self::SCREENS[$screen])) {
            $screen = 'general';
        }

        return redirect()
            ->route('admin.settings.edit', $screen === 'general' ? [] : ['screen' => $screen])
            ->with('success', 'تنظیمات ذخیره شد.');
    }
}
