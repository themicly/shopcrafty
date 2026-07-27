<?php

namespace Themicly\Shopcrafty\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

final class SetStorefrontLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('admin', 'admin/*')) {
            $locale = Schema::hasTable('settings')
                ? (string) settings('localization.language', config('app.locale', 'en'))
                : (string) config('app.locale', 'en');
            $available = config('shopcrafty.available_locales', []);

            if (isset($available[$locale])) {
                app()->setLocale($locale);
            }
        }

        return $next($request);
    }
}
