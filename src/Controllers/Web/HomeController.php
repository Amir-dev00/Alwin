<?php

namespace App\Controllers\Web;

use App\Controllers\Controller;
use App\Models\Partner;
use App\Models\Product;
use App\Models\Project;
use App\Support\Site;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $page = Site::page('home');

        return view('pages.home', [
            'page' => $page,
            'blocks' => $page?->blockMap() ?? [],
            'partners' => Partner::query()->with('image')->where('is_active', true)->orderBy('sort_order')->get(),
            'projects' => Project::query()->with('image')->where('status', 'published')->where('show_on_home', true)->orderBy('sort_order')->limit(6)->get(),
            'products' => Product::query()->with(['category', 'imageClose', 'imageOpen'])->where('status', 'published')->where('show_on_home', true)->orderBy('sort_order')->limit(4)->get(),
            'projectCount' => Project::query()->where('status', 'published')->count(),
        ]);
    }
}
