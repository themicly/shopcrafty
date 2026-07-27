<?php

use Themicly\Shopcrafty\Modules\Marketing\Models\Coupon;
use Themicly\Shopcrafty\Modules\Marketing\Services\CouponService;
use Themicly\Shopcrafty\Modules\Marketing\Services\NewsletterService;
use Themicly\Shopcrafty\Modules\Settings\Services\CurrencyService;
use Themicly\Shopcrafty\Modules\Settings\Services\Settings;
use Themicly\Shopcrafty\Tests\TestCase;

final class MarketingAndSettingsTest extends TestCase
{
    protected function migrateCore(): void
    {
        $this->artisan('migrate')->assertExitCode(0);
    }

    public function test_settings_persist_values_and_audit_real_changes_only(): void
    {
        $this->migrateCore();
        $settings = app(Settings::class);

        $settings->set('store.name', 'Shopcrafty');
        $settings->set('store.name', 'Shopcrafty');
        $settings->setMany(['store.name' => 'Shopcrafty Store', 'store.tagline' => 'Sell online']);

        $this->assertSame('Shopcrafty Store', $settings->get('store.name'));
        $this->assertSame(['name' => 'Shopcrafty Store', 'tagline' => 'Sell online'], $settings->group('store'));
        $this->assertCount(3, DB::table('setting_audits')->get());
    }

    public function test_currency_service_supports_conversion_and_display_position(): void
    {
        $this->migrateCore();
        app(Settings::class)->setMany([
            'localization.currency_code' => 'USD',
            'localization.currency_symbol' => '$',
            'localization.currency_decimals' => 2,
            'localization.currency_position' => 'before',
            'localization.currencies' => [['code' => 'EUR', 'symbol' => '€', 'rate' => 0.9, 'position' => 'after']],
        ]);

        $currency = app(CurrencyService::class);

        $this->assertTrue($currency->hasMultiple());
        $this->assertTrue($currency->setActive('EUR'));
        $this->assertSame('EUR', $currency->activeCode());
        $this->assertSame('9.00€', $currency->format(1000));
        $this->assertFalse($currency->setActive('GBP'));
    }

    public function test_coupon_service_validates_percentage_and_redeems_idempotently(): void
    {
        $this->migrateCore();
        $coupon = Coupon::create([
            'code' => 'SAVE10', 'name' => 'Ten percent', 'type' => 'percentage', 'value' => 10,
            'scope_type' => 'all', 'status' => 'active', 'is_enabled' => true, 'used_count' => 0,
        ]);

        $service = app(CouponService::class);
        $result = $service->validate(' save10 ', 10000);
        $service->redeem($coupon, 1, null, $result['discount']);
        $service->redeem($coupon, 1, null, $result['discount']);

        $this->assertTrue($result['ok']);
        $this->assertSame(1000, $result['discount']);
        $this->assertSame(1, $coupon->fresh()->used_count);
    }

    public function test_newsletter_subscription_is_idempotent_and_can_unsubscribe(): void
    {
        $this->migrateCore();
        $service = app(NewsletterService::class);
        $subscriber = $service->subscribe(' ADA@EXAMPLE.COM ', 'Ada');
        $same = $service->subscribe('ada@example.com', 'Ada Lovelace');

        $this->assertSame($subscriber->id, $same->id);
        $this->assertSame('Ada Lovelace', $same->name);
        $this->assertTrue($service->unsubscribe($same->token));
        $this->assertSame('unsubscribed', $same->fresh()->status);
        $this->assertFalse($service->unsubscribe('missing-token'));
    }
}
