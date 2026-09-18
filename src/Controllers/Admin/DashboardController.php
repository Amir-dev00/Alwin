<?php

namespace App\Controllers\Admin;

use App\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ContactInquiry;
use App\Models\Media;
use App\Models\PageBlock;
use App\Models\Product;
use App\Models\Project;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $productsTotal = Product::withTrashed()->count();
        $productsPublished = Product::query()->where('status', 'published')->count();
        $productsDraft = Product::query()->where('status', 'draft')->count();
        $projectsTotal = Project::withTrashed()->count();
        $projectsPublished = Project::query()->where('status', 'published')->count();
        $projectsDraft = Project::query()->where('status', 'draft')->count();

        $missingImages = Product::query()
            ->where(function ($q) {
                $q->whereNull('image_close_id')->orWhereNull('image_open_id');
            })
            ->count()
            + Project::query()->whereNull('image_id')->count();

        $incompleteSeo = Product::query()
            ->where(function ($q) {
                $q->whereNull('seo_title')->orWhere('seo_title', '')
                    ->orWhereNull('seo_description')->orWhere('seo_description', '');
            })
            ->count()
            + Project::query()
                ->where(function ($q) {
                    $q->whereNull('seo_title')->orWhere('seo_title', '')
                        ->orWhereNull('seo_description')->orWhere('seo_description', '');
                })
                ->count();

        return view('admin.dashboard', [
            'stats' => [
                'products_total' => $productsTotal,
                'products_published' => $productsPublished,
                'products_draft' => $productsDraft,
                'projects_total' => $projectsTotal,
                'projects_published' => $projectsPublished,
                'projects_draft' => $projectsDraft,
                'media' => Media::query()->count(),
                'missing_images' => $missingImages,
                'incomplete_seo' => $incompleteSeo,
                'new_inquiries' => ContactInquiry::query()->where('status', ContactInquiry::STATUS_NEW)->count(),
            ],
            'recentLogs' => ActivityLog::query()->with('user')->latest()->limit(10)->get(),
            'recentMedia' => Media::query()->latest()->limit(8)->get(),
            'recentInquiries' => ContactInquiry::query()->latest()->limit(5)->get(),
        ]);
    }
}
