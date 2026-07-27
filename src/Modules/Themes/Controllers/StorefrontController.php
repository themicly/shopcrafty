<?php

namespace Themicly\Shopcrafty\Modules\Themes\Controllers;

use Illuminate\Support\Facades\View;
use Themicly\Shopcrafty\Modules\Themes\Services\ThemeService;

class StorefrontController
{
    public function home(ThemeService $themes)
    {
        return View::make('theme::home', [
            'sections' => $themes->sections('home'),
        ]);
    }
}
