<?php

namespace App\View\Composers;

use App\Support\Site;
use App\Support\SiteCopy;
use Illuminate\View\View;

class SiteComposer
{
    public function compose(View $view): void
    {
        $view->with([
            'siteSettings' => Site::settings(),
            'headerNav' => Site::nav('header'),
            'footerNav' => Site::nav('footer'),
            'calculatorCopy' => SiteCopy::resolvedCalculator(),
        ]);
    }
}
