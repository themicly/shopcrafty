<?php

namespace Themicly\Shopcrafty\Modules\CMS\Controllers;

use Illuminate\Support\Facades\View;
use Themicly\Shopcrafty\Modules\CMS\Models\Page;

class StorefrontController
{
    public function page(string $slug)
    {
        // Live preview: an authenticated admin viewing ?preview=1 sees the page
        // even as a draft, with any unsaved builder changes from their session.
        $preview = request()->boolean('preview') && auth('web')->check();

        $page = ($preview ? Page::query() : Page::published())
            ->where('slug', $slug)
            ->firstOrFail();

        if ($preview && ($draft = session('cms_page_draft.'.$page->id))) {
            $page->blocks = $draft['blocks'] ?? $page->blocks;
            $page->title = $draft['title'] ?? $page->title;
        }

        return View::make('theme::page', compact('page'));
    }
}
