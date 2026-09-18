<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\ProductCategory;
use App\Support\Audit;
use App\Support\SiteCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductCategoryController extends Controller
{
    public function index(): View
    {
        return view('admin.categories.index', [
            'items' => ProductCategory::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ], ['name.required' => 'نام دسته الزامی است.']);
        $data['slug'] = $data['slug'] ?: Str::slug($data['name'], '-', 'fa');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $item = ProductCategory::query()->create($data);
        Audit::log('create', $item, 'ایجاد دسته '.$item->name);
        SiteCache::flush();
        return back()->with('success', 'دسته ذخیره شد.');
    }

    public function update(Request $request, ProductCategory $category): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $category->update($data);
        Audit::log('update', $category, 'ویرایش دسته '.$category->name);
        SiteCache::flush();
        return back()->with('success', 'دسته به‌روز شد.');
    }

    public function destroy(ProductCategory $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return back()->withErrors(['category' => 'ابتدا محصولات این دسته را منتقل کنید.']);
        }
        $category->delete();
        Audit::log('delete', $category, 'حذف دسته '.$category->name);
        SiteCache::flush();
        return back()->with('success', 'دسته حذف شد.');
    }
}
