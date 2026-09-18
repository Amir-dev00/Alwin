<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\Page;
use App\Models\PageBlock;
use App\Support\Audit;
use App\Support\AutoFill;
use App\Support\HtmlSanitizer;
use App\Support\SiteCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function index(): View
    {
        return view('admin.pages.index', [
            'pages' => Page::query()->withCount('blocks')->orderBy('title')->get(),
        ]);
    }

    public function home(): RedirectResponse
    {
        $page = Page::query()->where('key', 'home')->firstOrFail();

        return redirect()->route('admin.pages.edit', $page);
    }

    public function edit(Page $page): View
    {
        $page->load('blocks');
        return view('admin.pages.edit', ['page' => $page]);
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'seo_title' => ['nullable', 'string', 'max:190'],
            'seo_description' => ['nullable', 'string', 'max:320'],
            'blocks' => ['array'],
            'blocks.*.id' => ['required', 'integer'],
            'blocks.*.value' => ['nullable'],
            'order' => ['nullable', 'array'],
            'order.*' => ['integer'],
        ]);

        $page->update([
            'title' => $data['title'],
            'seo_title' => ($data['seo_title'] ?? null) ?: AutoFill::seoTitle($data['title']),
            'seo_description' => ($data['seo_description'] ?? null) ?: AutoFill::seoDescription(null, $data['title']),
        ]);

        foreach ($data['blocks'] ?? [] as $row) {
            $block = PageBlock::query()->where('page_id', $page->id)->where('id', $row['id'])->first();
            if (! $block) {
                continue;
            }
            $value = $row['value'] ?? '';
            if (is_array($value)) {
                $value = json_encode($value, JSON_UNESCAPED_UNICODE);
            }
            if ($block->type === 'html') {
                $value = HtmlSanitizer::clean((string) $value);
            }
            $block->value = $value;
            $block->save();
        }

        if (! empty($data['order'])) {
            foreach ($data['order'] as $i => $id) {
                PageBlock::query()->where('page_id', $page->id)->where('id', $id)->update(['sort_order' => $i]);
            }
        }

        Audit::log('update', $page, 'ویرایش صفحه '.$page->title);
        SiteCache::flush();
        return back()->with('success', 'محتوای صفحه ذخیره شد.');
    }
}
