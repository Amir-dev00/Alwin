<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\Media;
use App\Models\Partner;
use App\Support\Audit;
use App\Support\SiteCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartnerController extends Controller
{
    public function index(): View
    {
        return view('admin.partners.index', [
            'items' => Partner::query()->with('image')->orderBy('sort_order')->get(),
            'media' => Media::query()->where('kind', 'image')->orderBy('original_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'alt_text' => ['nullable', 'string', 'max:190'],
            'image_id' => ['nullable', 'exists:media,id'],
            'url' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ], ['name.required' => 'نام شریک الزامی است.']);
        $data['sort_order'] = (int) ($data['sort_order'] ?? ((int) Partner::query()->max('sort_order') + 1));
        $data['is_active'] = true;
        if (empty($data['alt_text'])) {
            $data['alt_text'] = $data['name'];
        }
        $item = Partner::query()->create($data);
        Audit::log('create', $item, 'افزودن لوگوی '.$item->name);
        SiteCache::flush();
        return back()->with('success', 'لوگو افزوده شد.');
    }

    public function update(Request $request, Partner $partner): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'alt_text' => ['nullable', 'string', 'max:190'],
            'image_id' => ['nullable', 'exists:media,id'],
            'url' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? $partner->sort_order);
        if (empty($data['alt_text'])) {
            $data['alt_text'] = $data['name'];
        }
        $partner->update($data);
        Audit::log('update', $partner, 'ویرایش لوگو '.$partner->name);
        SiteCache::flush();
        return back()->with('success', 'لوگو به‌روز شد.');
    }

    public function destroy(Partner $partner): RedirectResponse
    {
        $partner->delete();
        Audit::log('delete', $partner, 'حذف لوگو');
        SiteCache::flush();
        return back()->with('success', 'لوگو حذف شد.');
    }
}
