<?php

use Themicly\Shopcrafty\Core\Module\AddonRegistry;
use Themicly\Shopcrafty\Modules\Themes\Models\Theme;
use Themicly\Shopcrafty\Modules\Themes\Services\ThemeService;
use Themicly\Shopcrafty\Tests\TestCase;

final class PackageTest extends TestCase
{
    public function test_package_configuration_and_registry_are_available(): void
    {
        $this->assertIsArray(config('shopcrafty'));
        $this->assertTrue(app()->bound(AddonRegistry::class));
        $this->assertArrayNotHasKey('currency', config('shopcrafty'));
        $this->assertArrayNotHasKey('default_demo_pack', config('shopcrafty'));
        $this->assertNull(config('demo-packs'));
    }

    public function test_storefront_route_is_registered(): void
    {
        $this->assertSame('/', route('shopcrafty.storefront', absolute: false));
    }

    public function test_addon_registry_supports_storefront_contributions(): void
    {
        $registry = app(AddonRegistry::class);
        $registry->register('demo-addon', ['name' => 'Demo add-on']);
        $registry->registerStorefrontFeature('footer', 'demo-link', [
            'label' => 'Demo',
            'route' => 'shopcrafty.storefront',
        ]);

        $this->assertTrue($registry->installed('demo-addon'));
        $this->assertSame('Demo', $registry->storefrontFeatures('footer')['demo-link']['label']);
    }

    public function test_host_theme_root_has_priority_over_package_themes(): void
    {
        config(['shopcrafty.themes_path' => __DIR__.'/../Fixtures/themes']);

        $themes = app(ThemeService::class);

        $this->assertSame(__DIR__.'/../Fixtures/themes', $themes->themeRoots()[0]);
        $this->assertSame(__DIR__.'/../Fixtures/themes/market', $themes->themePath('market'));
        $this->assertSame('', $themes->themePath('../market'));
        $this->assertSame('Host Marketplace', $themes->metadata('market')['name']);
    }

    public function test_addon_features_are_absent_until_registered(): void
    {
        $registry = app(AddonRegistry::class);

        $this->assertFalse($registry->installed('cookie-consent'));
        $this->assertSame([], $registry->storefrontFeatures('privacy'));

        $registry->register('cookie-consent');
        $registry->registerStorefrontFeature('privacy', 'cookie-consent', ['label' => 'Privacy & consent']);

        $this->assertTrue($registry->installed('cookie-consent'));
        $this->assertArrayHasKey('cookie-consent', $registry->storefrontFeatures('privacy'));
    }

    public function test_registry_keeps_search_and_settings_contributions_isolated(): void
    {
        $registry = app(AddonRegistry::class);
        $registry->registerSearchType('popular', ['label' => 'Popular searches']);
        $registry->registerSettingsSchema('reviews', ['enabled' => ['type' => 'boolean']]);

        $this->assertSame('Popular searches', $registry->searchTypes()['popular']['label']);
        $this->assertSame('boolean', $registry->settingsSchemas()['reviews']['enabled']['type']);
        $this->assertSame([], $registry->storefrontFeatures('header'));
    }

    public function test_optional_settings_are_disabled_until_the_addon_is_installed(): void
    {
        $this->assertFalse(settings('catalog.reviews_enabled', true));
        $this->assertFalse(settings('privacy.cookie_consent_enabled', true));
        $this->assertSame([], settings('search.popular_terms', ['fallback']));
    }

    public function test_theme_sync_registers_host_and_package_themes_without_duplicates(): void
    {
        $this->artisan('migrate');
        config(['shopcrafty.themes_path' => __DIR__.'/../Fixtures/themes']);

        $service = app(ThemeService::class);
        $registered = $service->syncFromDisk();

        $this->assertSame($registered, Theme::query()->count());
        $this->assertSame('Host Marketplace', Theme::query()->where('slug', 'market')->value('name'));
        $this->assertSame(1, Theme::query()->where('slug', 'market')->count());
        $this->assertTrue(Theme::query()->where('is_active', true)->exists());
    }

    public function test_vendor_themes_are_hidden_from_selectable_catalog_by_default(): void
    {
        $this->artisan('migrate');

        app(ThemeService::class)->syncFromDisk();

        $this->assertTrue(Theme::whereIn('slug', ThemeService::OPEN_SOURCE_THEMES)->count() >= 2);
        $this->assertFalse(Theme::whereIn('slug', ['default', 'fresh', 'haven', 'studio'])->exists());
    }
}
