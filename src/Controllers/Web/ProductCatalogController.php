<?php

namespace App\Controllers\Web;

use App\Controllers\Controller;
use App\Models\Product;
use App\Support\Site;
use Illuminate\Contracts\View\View;

class ProductCatalogController extends Controller
{
    public function __invoke(): View
    {
        $page = Site::page('services');

        return view('products.index', [
            'page' => $page,
            'blocks' => $page?->blockMap() ?? [],
            'productCount' => Product::query()->where('status', 'published')->count(),
        ]);
    }
}
