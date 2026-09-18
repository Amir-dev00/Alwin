<?php

namespace App\Controllers\Web;

use App\Controllers\Controller;
use App\Support\Site;
use Illuminate\Contracts\View\View;

class ContactController extends Controller
{
    public function __invoke(): View
    {
        $page = Site::page('contact');

        return view('pages.contact', [
            'page' => $page,
            'blocks' => $page?->blockMap() ?? [],
        ]);
    }
}
