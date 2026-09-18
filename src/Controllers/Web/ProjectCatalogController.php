<?php

namespace App\Controllers\Web;

use App\Controllers\Controller;
use App\Models\Project;
use App\Support\Site;
use Illuminate\Contracts\View\View;

class ProjectCatalogController extends Controller
{
    public function index(): View
    {
        $page = Site::page('portfolio');

        return view('projects.index', [
            'page' => $page,
            'blocks' => $page?->blockMap() ?? [],
            'projectCount' => Project::query()->where('status', 'published')->count(),
        ]);
    }

    public function show(Project $project): View
    {
        abort_unless($project->isPublished(), 404);
        $project->load('image');

        $siblings = Project::query()
            ->where('status', 'published')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get(['id', 'slug', 'title']);

        $index = $siblings->search(fn (Project $item) => $item->id === $project->id);
        $prev = $index !== false ? $siblings->get($index - 1) : null;
        $next = $index !== false ? $siblings->get($index + 1) : null;

        $page = Site::page('project');

        return view('projects.show', [
            'project' => $project,
            'page' => $page,
            'blocks' => $page?->blockMap() ?? [],
            'prev' => $prev,
            'next' => $next,
            'related' => Project::query()->with('image')->where('status', 'published')->where('id', '!=', $project->id)->orderBy('sort_order')->limit(3)->get(),
        ]);
    }
}
