<?php

namespace App\Controllers\Web;

use App\Controllers\Controller;
use App\Support\Site;
use Illuminate\Contracts\View\View;

class AboutController extends Controller
{
    public function __invoke(): View
    {
        $page = Site::page('about');

        return view('pages.about', [
            'page' => $page,
            'blocks' => $page?->blockMap() ?? [],
        ]);
    }
}
