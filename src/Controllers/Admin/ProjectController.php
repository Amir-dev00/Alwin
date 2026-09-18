<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\Media;
use App\Models\Project;
use App\Support\Audit;
use App\Support\AutoFill;
use App\Support\SiteCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Project::class);
        $q = Project::query()->with('image');
        if ($request->filled('search')) {
            $s = $request->string('search')->toString();
            $q->where(function ($qq) use ($s) {
                $qq->where('title', 'like', "%{$s}%")
                    ->orWhere('location', 'like', "%{$s}%")
                    ->orWhere('client_name', 'like', "%{$s}%");
            });
        }
        if ($request->filled('status')) {
            $q->where('status', $request->string('status'));
        }
        if ($request->boolean('trashed')) {
            $q->onlyTrashed();
        }
        if ($request->filled('listing')) {
            $q->where('show_on_listing', $request->string('listing')->toString() === '1');
        }

        return view('admin.projects.index', [
            'items' => $q->orderBy('sort_order')->paginate(12)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Project::class);
        return view('admin.projects.form', [
            'item' => new Project([
                'status' => 'published',
                'type' => 'upvc',
                'type_label' => 'پنجره UPVC',
                'show_on_home' => true,
                'show_on_listing' => true,
                'sort_order' => (int) Project::query()->max('sort_order') + 1,
            ]),
            'media' => Media::query()->where('kind', 'image')->orderBy('original_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Project::class);
        $item = Project::query()->create($this->validated($request));
        Audit::log('create', $item, 'ایجاد پروژه '.$item->title);
        SiteCache::flush();
        return redirect()->route('admin.projects.edit', $item)->with('success', 'پروژه ذخیره شد.');
    }

    public function edit(Project $project): View
    {
        $this->authorize('update', $project);
        $project->load('image');
        return view('admin.projects.form', [
            'item' => $project,
            'media' => Media::query()->where('kind', 'image')->orderBy('original_name')->get(),
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);
        $project->update($this->validated($request, $project));
        Audit::log('update', $project, 'ویرایش پروژه '.$project->title);
        SiteCache::flush();
        return back()->with('success', 'تغییرات پروژه ذخیره شد.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);
        $title = $project->title;
        $project->delete();
        Audit::log('delete', $project, 'حذف پروژه '.$title);
        SiteCache::flush();
        return redirect()->route('admin.projects.index')->with('success', 'پروژه به سطل زباله منتقل شد.');
    }

    public function restore(int $id): RedirectResponse
    {
        $item = Project::onlyTrashed()->findOrFail($id);
        $this->authorize('update', $item);
        $item->restore();
        Audit::log('restore', $item, 'بازیابی پروژه '.$item->title);
        SiteCache::flush();
        return back()->with('success', 'پروژه بازیابی شد.');
    }

    public function duplicate(Project $project): RedirectResponse
    {
        $this->authorize('create', Project::class);
        $copy = $project->replicate();
        $copy->title = $project->title.' (کپی)';
        $copy->slug = Str::slug($copy->title, '-', 'fa').'-'.time();
        $copy->status = 'draft';
        $copy->save();
        Audit::log('duplicate', $copy, 'کپی از پروژه '.$project->title);
        SiteCache::flush();
        return redirect()->route('admin.projects.edit', $copy)->with('success', 'نسخه پیش‌نویس ساخته شد.');
    }

    public function listing(Request $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);
        $project->update(['show_on_listing' => $request->boolean('show_on_listing')]);
        Audit::log('update', $project, 'نمایش فهرست پروژه '.$project->title);
        SiteCache::flush();

        return back()->with('success', $project->show_on_listing ? 'پروژه در صفحه پروژه‌ها نمایش داده می‌شود.' : 'پروژه از صفحه پروژه‌ها برداشته شد.');
    }

    private function validated(Request $request, ?Project $project = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190', Rule::unique('projects', 'slug')->ignore($project?->id)],
            'type' => ['nullable', 'string', 'max:64'],
            'type_label' => ['nullable', 'string', 'max:120'],
            'summary' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'image_id' => ['nullable', 'exists:media,id'],
            'alt_text' => ['nullable', 'string', 'max:190'],
            'client_name' => ['nullable', 'string', 'max:190'],
            'location' => ['nullable', 'string', 'max:190'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'show_on_home' => ['sometimes', 'boolean'],
            'show_on_listing' => ['sometimes', 'boolean'],
            'status' => ['required', 'in:draft,published'],
            'seo_title' => ['nullable', 'string', 'max:190'],
            'seo_description' => ['nullable', 'string', 'max:320'],
        ], ['title.required' => 'عنوان پروژه الزامی است.']);

        $data['show_on_home'] = $request->boolean('show_on_home');
        $data['show_on_listing'] = $request->boolean('show_on_listing');
        $data['sort_order'] = (int) ($data['sort_order'] ?? ($project?->sort_order ?: Project::query()->max('sort_order') + 1));
        if (empty($data['slug'])) {
            $data['slug'] = AutoFill::slug($data['title'], 'project');
        }
        if (empty($data['type_label'])) {
            $data['type_label'] = 'پنجره UPVC';
        }
        if (empty($data['type'])) {
            $data['type'] = AutoFill::latinKey($data['type_label'], 'upvc');
        }
        if (empty($data['seo_title'])) {
            $data['seo_title'] = AutoFill::seoTitle($data['title']);
        }
        if (empty($data['seo_description'])) {
            $data['seo_description'] = AutoFill::seoDescription($data['summary'] ?? null, $data['title']);
        }
        if (empty($data['alt_text'])) {
            $data['alt_text'] = $data['title'];
        }
        return $data;
    }
}
