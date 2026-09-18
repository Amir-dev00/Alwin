<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\Audit;
use App\Support\AutoFill;
use App\Support\SiteCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);
        $q = Product::query()->with(['category', 'imageClose', 'imageOpen']);
        if ($request->filled('search')) {
            $s = $request->string('search')->toString();
            $q->where(function ($qq) use ($s) {
                $qq->where('name', 'like', "%{$s}%")->orWhere('slug', 'like', "%{$s}%");
            });
        }
        if ($request->filled('status')) {
            $q->where('status', $request->string('status'));
        }
        if ($request->filled('category_id')) {
            $q->where('category_id', $request->integer('category_id'));
        }
        if ($request->boolean('trashed')) {
            $q->onlyTrashed();
        }
        if ($request->filled('listing')) {
            $q->where('show_on_listing', $request->string('listing')->toString() === '1');
        }
        $sort = $request->string('sort')->toString() ?: 'sort_order';
        $dir = $request->string('dir')->toString() === 'desc' ? 'desc' : 'asc';
        if (! in_array($sort, ['name', 'sort_order', 'created_at', 'status'], true)) {
            $sort = 'sort_order';
        }

        return view('admin.products.index', [
            'items' => $q->orderBy($sort, $dir)->paginate(12)->withQueryString(),
            'categories' => ProductCategory::query()->orderBy('sort_order')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);
        return view('admin.products.form', [
            'item' => new Product([
                'status' => 'published',
                'show_on_home' => false,
                'show_on_listing' => true,
                'sort_order' => (int) Product::query()->max('sort_order') + 1,
                'folder_number' => (int) Product::query()->max('folder_number') + 1,
            ]),
            'categories' => ProductCategory::query()->orderBy('sort_order')->get(),
            'media' => Media::query()->where('kind', 'image')->orderBy('original_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Product::class);
        $item = Product::query()->create($this->validated($request));
        Audit::log('create', $item, 'ایجاد محصول '.$item->name);
        SiteCache::flush();
        return redirect()->route('admin.products.edit', $item)->with('success', 'محصول ذخیره شد.');
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);
        $product->load(['imageClose', 'imageOpen', 'category']);
        return view('admin.products.form', [
            'item' => $product,
            'categories' => ProductCategory::query()->orderBy('sort_order')->get(),
            'media' => Media::query()->where('kind', 'image')->orderBy('original_name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);
        $product->update($this->validated($request, $product));
        Audit::log('update', $product, 'ویرایش محصول '.$product->name);
        SiteCache::flush();
        return back()->with('success', 'تغییرات محصول ذخیره شد.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);
        $name = $product->name;
        $product->delete();
        Audit::log('delete', $product, 'حذف محصول '.$name);
        SiteCache::flush();
        return redirect()->route('admin.products.index')->with('success', 'محصول به سطل زباله منتقل شد.');
    }

    public function restore(int $id): RedirectResponse
    {
        $item = Product::onlyTrashed()->findOrFail($id);
        $this->authorize('update', $item);
        $item->restore();
        Audit::log('restore', $item, 'بازیابی محصول '.$item->name);
        SiteCache::flush();
        return back()->with('success', 'محصول بازیابی شد.');
    }

    public function duplicate(Product $product): RedirectResponse
    {
        $this->authorize('create', Product::class);
        $copy = $product->replicate();
        $copy->name = $product->name.' (کپی)';
        $copy->slug = Str::slug($copy->name, '-', 'fa').'-'.time();
        $copy->status = 'draft';
        $copy->save();
        Audit::log('duplicate', $copy, 'کپی از محصول '.$product->name);
        SiteCache::flush();
        return redirect()->route('admin.products.edit', $copy)->with('success', 'نسخه پیش‌نویس ساخته شد.');
    }

    public function listing(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);
        $product->update(['show_on_listing' => $request->boolean('show_on_listing')]);
        Audit::log('update', $product, 'نمایش فهرست محصول '.$product->name);
        SiteCache::flush();

        return back()->with('success', $product->show_on_listing ? 'محصول در صفحه محصولات نمایش داده می‌شود.' : 'محصول از صفحه محصولات برداشته شد.');
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', Rule::unique('products', 'slug')->ignore($product?->id)],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'product_type' => ['nullable', 'string', 'max:64'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'specifications_text' => ['nullable', 'string'],
            'image_close_id' => ['nullable', 'exists:media,id'],
            'image_open_id' => ['nullable', 'exists:media,id'],
            'alt_text' => ['nullable', 'string', 'max:190'],
            'folder_number' => ['nullable', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'show_on_home' => ['sometimes', 'boolean'],
            'show_on_listing' => ['sometimes', 'boolean'],
            'status' => ['required', 'in:draft,published'],
            'seo_title' => ['nullable', 'string', 'max:190'],
            'seo_description' => ['nullable', 'string', 'max:320'],
        ], [
            'name.required' => 'نام محصول الزامی است.',
            'status.in' => 'وضعیت نامعتبر است.',
        ]);

        $data['show_on_home'] = $request->boolean('show_on_home');
        $data['show_on_listing'] = $request->boolean('show_on_listing');
        $data['sort_order'] = (int) ($data['sort_order'] ?? ($product?->sort_order ?: Product::query()->max('sort_order') + 1));
        if (! empty($data['specifications_text'])) {
            $lines = preg_split('/\r\n|\r|\n/', $data['specifications_text']) ?: [];
            $data['specifications'] = array_values(array_filter(array_map('trim', $lines)));
        } else {
            $data['specifications'] = [];
        }
        unset($data['specifications_text']);
        if (empty($data['slug'])) {
            $data['slug'] = AutoFill::slug($data['name'], 'product');
        }
        if (empty($data['seo_title'])) {
            $data['seo_title'] = AutoFill::seoTitle($data['name']);
        }
        if (empty($data['seo_description'])) {
            $data['seo_description'] = AutoFill::seoDescription($data['short_description'] ?? null, $data['name']);
        }
        if (empty($data['alt_text'])) {
            $data['alt_text'] = $data['name'];
        }
        $categoryName = null;
        if (! empty($data['category_id'])) {
            $categoryName = ProductCategory::query()->whereKey($data['category_id'])->value('name');
        }
        $data['product_type'] = AutoFill::productType($categoryName, $data['product_type'] ?? null);
        if (empty($data['folder_number'])) {
            $data['folder_number'] = $product?->folder_number ?: ((int) Product::query()->max('folder_number') + 1);
        }
        return $data;
    }
}
