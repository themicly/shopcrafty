<?php

namespace Themicly\Shopcrafty\Modules\Themes;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Themicly\Shopcrafty\Core\Module\ModuleServiceProvider;
use Themicly\Shopcrafty\Modules\Themes\Services\ThemeService;

class ThemesServiceProvider extends ModuleServiceProvider
{
    protected function moduleName(): string
    {
        return 'Themes';
    }

    protected function modulePath(): string
    {
        return __DIR__;
    }

    protected function registerModule(): void
    {
        $this->app->singleton(ThemeService::class);
    }

    protected function bootModule(): void
    {
        $theme = $this->app->make(ThemeService::class);
        $slug = $theme->activeSlug();

        // Storefront views render through `theme::` with fallback to Boutique,
        // so an active theme can override any view (marketplace-ready).
        $paths = array_values(array_unique(array_filter([
            $theme->themePath($slug).'/views',
            $theme->themePath('boutique').'/views',
        ], 'is_dir')));

        if (! empty($paths)) {
            foreach ($paths as $path) {
                $this->loadViewsFrom($path, 'theme');
            }

            // Storefront component kit: <x-st.product-card />, <x-st.price />, etc.
            // Components live in themes/{slug}/views/components/st/, resolved via dot syntax.
            // Register every theme path (active first, default last) so a theme can
            // override individual components while unlisted ones fall back to default.
            foreach ($paths as $path) {
                if (is_dir($componentPath = $path.'/components')) {
                    Blade::anonymousComponentPath($componentPath);
                }
            }
        }

        // Expose theme settings to every storefront view.
        View::composer('theme::*', function ($view) use ($theme) {
            $view->with('theme', $theme->settings());
        });
    }
}
