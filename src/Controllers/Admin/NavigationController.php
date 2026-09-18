<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\NavigationItem;
use App\Models\Page;
use App\Support\Audit;
use App\Support\SiteCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NavigationController extends Controller
{
    public function index(): View
    {
        return view('admin.navigation.index', [
            'header' => NavigationItem::query()->where('location', 'header')->orderBy('sort_order')->get(),
            'footer' => NavigationItem::query()->where('location', 'footer')->orderBy('sort_order')->get(),
            'pages' => Page::query()->orderBy('title')->get(['title', 'path']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'location' => ['required', 'in:header,footer'],
            'label' => ['required', 'string', 'max:120'],
            'url' => ['required', 'string', 'max:255'],
        ], ['label.required' => 'برچسب الزامی است.', 'url.required' => 'آدرس الزامی است.']);
        $max = (int) NavigationItem::query()->where('location', $data['location'])->max('sort_order');
        $item = NavigationItem::query()->create($data + ['sort_order' => $max + 1, 'is_active' => true]);
        Audit::log('create', $item, 'افزودن لینک منو '.$item->label);
        SiteCache::flush();
        return back()->with('success', 'لینک افزوده شد.');
    }

    public function update(Request $request, NavigationItem $navigation): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'url' => ['required', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? $navigation->sort_order);
        $navigation->update($data);
        Audit::log('update', $navigation, 'ویرایش لینک منو');
        SiteCache::flush();
        return back()->with('success', 'منو به‌روز شد.');
    }

    public function destroy(NavigationItem $navigation): RedirectResponse
    {
        $navigation->delete();
        Audit::log('delete', $navigation, 'حذف لینک منو');
        SiteCache::flush();
        return back()->with('success', 'لینک حذف شد.');
    }

    public function reorder(Request $request): RedirectResponse
    {
        $ids = $request->validate(['order' => ['required', 'array'], 'order.*' => ['integer']])['order'];
        foreach ($ids as $i => $id) {
            NavigationItem::query()->where('id', $id)->update(['sort_order' => $i]);
        }
        SiteCache::flush();
        return back()->with('success', 'ترتیب منو ذخیره شد.');
    }
}
