<?php

namespace Themicly\Shopcrafty;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Themicly\Shopcrafty\Core\Module\AddonRegistry;
use Themicly\Shopcrafty\Core\Navigation\NavigationRegistry;
use Themicly\Shopcrafty\Models\User;
use Themicly\Shopcrafty\Modules\Customers\Models\Customer;
use Themicly\Shopcrafty\Providers\AuthServiceProvider;

final class ShopcraftyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AddonRegistry::class);
        $this->app->singleton(NavigationRegistry::class);
        $this->mergeConfigFrom(__DIR__.'/../config/shopcrafty.php', 'shopcrafty');
        $this->mergeConfigFrom(__DIR__.'/../config/navigation.php', 'navigation');
        $this->mergeConfigFrom(__DIR__.'/../config/presets.php', 'presets');
        config([
            'auth.guards.customer' => ['driver' => 'session', 'provider' => 'customers'],
            'auth.providers.customers' => ['driver' => 'eloquent', 'model' => Customer::class],
            'auth.providers.users' => ['driver' => 'eloquent', 'model' => User::class],
        ]);

        foreach ([
            AuthServiceProvider::class,
            Modules\Settings\SettingsServiceProvider::class,
            Modules\Catalog\CatalogServiceProvider::class,
            Modules\Customers\CustomersServiceProvider::class,
            Modules\Orders\OrdersServiceProvider::class,
            Modules\Themes\ThemesServiceProvider::class,
            Modules\CMS\CMSServiceProvider::class,
            Modules\Marketing\MarketingServiceProvider::class,
            Modules\Reports\ReportsServiceProvider::class,
            Modules\Notifications\NotificationsServiceProvider::class,
        ] as $provider) {
            $this->app->register($provider);
        }
    }

    public function boot(): void
    {
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute((int) config('shopcrafty.auth_rate_limit', 10))
                ->by($request->ip());
        });

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'shopcrafty');
        // Theme templates use the normal Laravel groups (storefront.*, account.*,
        // checkout.*), so add the package language path to the root loader.
        Lang::addPath(__DIR__.'/../resources/lang');
        // Internal module routes use the conventional admin.* view names. Keep
        // the package root views available to those routes without requiring
        // every extracted route file to know the package namespace.
        View::addLocation(__DIR__.'/../resources/views');
        Blade::anonymousComponentPath(__DIR__.'/../resources/views/components');
        $this->loadRoutesFrom(__DIR__.'/Routes/web.php');
        // Laravel's default routes/web.php commonly contains a placeholder `/`
        // route. The host route file loads after package routes and can replace
        // the package route (including its name), which breaks theme previews
        // that generate signed storefront URLs. Restore the canonical route
        // after all application routes have loaded.
        $this->app->booted(function (): void {
            if (! Route::has('shopcrafty.storefront')) {
                Route::middleware(['web', Http\Middleware\SetStorefrontLocale::class, Http\Middleware\ApplyThemePreview::class])
                    ->get('/', [Modules\Themes\Controllers\StorefrontController::class, 'home'])
                    ->name('shopcrafty.storefront');
            }
        });
        $this->publishes([
            __DIR__.'/../config/shopcrafty.php' => config_path('shopcrafty.php'),
            __DIR__.'/../config/navigation.php' => config_path('navigation.php'),
            __DIR__.'/../config/presets.php' => config_path('presets.php'),
        ], 'shopcrafty-config');

        if ($this->app->runningInConsole()) {
            $this->commands([Console\Commands\InstallCommand::class]);
        }
    }
}
