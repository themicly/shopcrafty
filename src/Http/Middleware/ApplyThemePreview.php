<?php

namespace Themicly\Shopcrafty\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;
use Themicly\Shopcrafty\Modules\Themes\Services\ThemeService;

/**
 * Apply a signed, request-only theme override used by the admin preview cards.
 *
 * The override is intentionally restricted to the two selectable open-source
 * themes and never changes the active theme or any stored settings.
 */
class ApplyThemePreview
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->filled('_theme')) {
            abort_unless($request->hasValidSignature(), 403);

            $slug = (string) $request->query('_theme');
            $themes = app(ThemeService::class);
            abort_unless($themes->themePath($slug) !== '', 404);
            $themes->setPreviewSlug($slug);

            // The theme provider registers the active theme during boot. Put
            // the requested theme first for this request so the preview
            // resolves its own layout and sections.
            $path = $themes->themePath($slug).'/views';
            if (is_dir($path)) {
                View::getFinder()->prependNamespace('theme', $path);
            }
        }

        return $next($request);
    }
}
