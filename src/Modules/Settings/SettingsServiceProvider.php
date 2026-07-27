<?php

namespace Themicly\Shopcrafty\Modules\Settings;

use Themicly\Shopcrafty\Core\Module\ModuleServiceProvider;
use Themicly\Shopcrafty\Modules\Settings\Services\CurrencyService;
use Themicly\Shopcrafty\Modules\Settings\Services\Settings;

class SettingsServiceProvider extends ModuleServiceProvider
{
    protected function moduleName(): string
    {
        return 'Settings';
    }

    protected function modulePath(): string
    {
        return __DIR__;
    }

    protected function registerModule(): void
    {
        // One instance per request so the settings blob is loaded once.
        $this->app->singleton(Settings::class);

        // One instance per request so the currency list is resolved once.
        $this->app->singleton(CurrencyService::class);
    }
}
