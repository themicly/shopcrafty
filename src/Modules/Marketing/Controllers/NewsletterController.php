<?php

namespace Themicly\Shopcrafty\Modules\Marketing\Controllers;

use Illuminate\Support\Facades\View;
use Themicly\Shopcrafty\Modules\Marketing\Services\NewsletterService;

class NewsletterController
{
    public function unsubscribe(string $token, NewsletterService $newsletter)
    {
        $ok = $newsletter->unsubscribe($token);

        return View::make('theme::newsletter-unsubscribe', ['ok' => $ok]);
    }
}
