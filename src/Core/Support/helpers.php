<?php

use Themicly\Shopcrafty\Core\Module\AddonRegistry;
use Themicly\Shopcrafty\Core\Support\DemoMode;
use Themicly\Shopcrafty\Modules\Settings\Services\CurrencyService;
use Themicly\Shopcrafty\Modules\Settings\Services\Settings;

if (! function_exists('settings')) {
    /**
     * Resolve the Settings service, or read a single value when a key is given.
     */
    function settings(?string $key = null, mixed $default = null): mixed
    {
        $service = app(Settings::class);

        if ($key === null) {
            return $service;
        }

        $optionalSettings = [
            'catalog.reviews_enabled' => 'reviews',
            'catalog.wishlist_enabled' => 'wishlist',
            'catalog.compare_enabled' => 'compare',
            'search.popular_terms' => 'popular-search',
            'privacy.cookie_consent_enabled' => 'cookie-consent',
        ];

        if (isset($optionalSettings[$key]) && ! app(AddonRegistry::class)->installed($optionalSettings[$key])) {
            return is_array($default) ? [] : false;
        }

        return $service->get($key, $default);
    }
}

if (! function_exists('text_direction')) {
    /**
     * 'rtl' or 'ltr' for the <html dir="…"> attribute — a manual admin choice
     * (Settings → Localization), not inferred from the selected language.
     *
     * With demo_ui mode on (see DemoMode::uiEnabled()), an unsigned
     * `?dir=ltr|rtl` overrides the admin setting for just this request —
     * mirrors ApplyThemePreview's `?theme=` override, never persisted.
     */
    function text_direction(): string
    {
        $request = request();

        if (! $request->is('admin', 'admin/*') && DemoMode::uiEnabled()) {
            $override = (string) $request->query('dir', '');

            if (in_array($override, ['ltr', 'rtl'], true)) {
                return $override;
            }
        }

        return (string) settings('localization.text_direction', 'ltr') === 'rtl' ? 'rtl' : 'ltr';
    }
}

if (! function_exists('format_money')) {
    /**
     * Format an amount stored in minor units (poisha/cents) using store currency settings.
     */
    function format_money(int $minor, ?string $symbol = null): string
    {
        // Delegate to the currency service so the storefront can display a converted
        // currency while admin/orders stay in base. Falls back to plain base
        // formatting if the container/service isn't available (e.g. install time).
        try {
            return app(CurrencyService::class)->format($minor, $symbol);
        } catch (Throwable $e) {
            $decimals = (int) settings('localization.currency_decimals', 2);
            $symbol ??= (string) settings('localization.currency_symbol', '$');
            $position = (string) settings('localization.currency_position', 'before');

            $amount = number_format($minor / (10 ** $decimals), $decimals);

            return $position === 'after' ? "{$amount}{$symbol}" : "{$symbol}{$amount}";
        }
    }
}
