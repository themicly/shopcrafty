<?php

namespace Themicly\Shopcrafty\Modules\Settings\Actions;

use InvalidArgumentException;
use Themicly\Shopcrafty\Modules\Settings\Services\Settings;

/**
 * Apply a country preset's regional defaults into the localization settings group.
 * Region-dataset seeding (geo tables) happens in Session 8.
 */
class ApplyCountryPreset
{
    public function __construct(private Settings $settings) {}

    public function handle(string $country): void
    {
        $preset = config("presets.countries.{$country}");

        if (! $preset) {
            throw new InvalidArgumentException("Unknown country preset [{$country}].");
        }

        $this->settings->setMany([
            'localization.country' => $country,
            'localization.currency_code' => $preset['currency_code'],
            'localization.currency_symbol' => $preset['currency_symbol'],
            'localization.currency_position' => $preset['currency_position'],
            'localization.currency_decimals' => $preset['currency_decimals'],
            'localization.timezone' => $preset['timezone'],
            'localization.date_format' => $preset['date_format'],
            'localization.phone_country_code' => $preset['phone_country_code'],
            'localization.region_dataset' => $preset['region_dataset'],
            'localization.weight_unit' => $preset['weight_unit'],
        ]);
    }
}
