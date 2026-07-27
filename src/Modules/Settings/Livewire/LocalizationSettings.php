<?php

namespace Themicly\Shopcrafty\Modules\Settings\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Settings\Actions\ApplyCountryPreset;
use Themicly\Shopcrafty\Modules\Settings\Services\Settings;

class LocalizationSettings extends Component
{
    public string $country = 'generic';

    public string $currencyCode = 'USD';

    public string $currencySymbol = '$';

    public string $currencyPosition = 'before';

    public int $currencyDecimals = 2;

    /** @var array<int, array{code:string,symbol:string,rate:string}> Extra display currencies. */
    public array $currencies = [];

    public string $timezone = 'UTC';

    public string $dateFormat = 'M j, Y';

    public string $weightUnit = 'kg';

    /** The single language the whole storefront renders in — no visitor-facing switcher. */
    public string $language = 'en';

    /** Manual choice, independent of language — not inferred from it. */
    public string $textDirection = 'ltr';

    public function mount(Settings $settings): void
    {
        $this->loadFrom($settings);
    }

    protected function loadFrom(Settings $settings): void
    {
        $this->country = (string) $settings->get('localization.country', config('presets.default'));
        $this->currencyCode = (string) $settings->get('localization.currency_code', 'USD');
        $this->currencySymbol = (string) $settings->get('localization.currency_symbol', '$');
        $this->currencyPosition = (string) $settings->get('localization.currency_position', 'before');
        $this->currencyDecimals = (int) $settings->get('localization.currency_decimals', 2);
        $this->currencies = array_map(fn ($c) => [
            'code' => (string) ($c['code'] ?? ''),
            'symbol' => (string) ($c['symbol'] ?? ''),
            'rate' => (string) ($c['rate'] ?? ''),
        ], (array) $settings->get('localization.currencies', []));
        $this->timezone = (string) $settings->get('localization.timezone', 'UTC');
        $this->dateFormat = (string) $settings->get('localization.date_format', 'M j, Y');
        $this->weightUnit = (string) $settings->get('localization.weight_unit', 'kg');
        $this->language = (string) $settings->get('localization.language', config('app.locale'));
        $this->textDirection = (string) $settings->get('localization.text_direction', 'ltr');
    }

    /** Apply the selected country's regional defaults, then refresh the form. */
    public function applyPreset(ApplyCountryPreset $action, Settings $settings): void
    {
        $action->handle($this->country);
        $this->loadFrom($settings);

        $this->dispatch('toast', message: 'Preset applied — review and save.', type: 'success');
    }

    public function addCurrency(): void
    {
        $this->currencies[] = ['code' => '', 'symbol' => '', 'rate' => ''];
    }

    public function removeCurrency(int $index): void
    {
        unset($this->currencies[$index]);
        $this->currencies = array_values($this->currencies);
    }

    public function save(Settings $settings): void
    {
        $data = $this->validate([
            'country' => ['required', 'string'],
            'currencyCode' => ['required', 'string', 'size:3'],
            'currencySymbol' => ['required', 'string', 'max:8'],
            'currencyPosition' => ['required', 'in:before,after'],
            'currencyDecimals' => ['required', 'integer', 'min:0', 'max:4'],
            'currencies' => ['array'],
            'currencies.*.code' => ['required', 'string', 'size:3'],
            'currencies.*.symbol' => ['required', 'string', 'max:8'],
            'currencies.*.rate' => ['required', 'numeric', 'gt:0'],
            'timezone' => ['required', 'string', 'timezone'],
            'dateFormat' => ['required', 'string', 'max:32'],
            'weightUnit' => ['required', 'in:kg,g,lb'],
            'language' => ['required', 'string', 'in:'.implode(',', array_keys(config('shopcrafty.available_locales', [])))],
            'textDirection' => ['required', 'in:ltr,rtl'],
        ]);

        // Normalize extra currencies; drop any that duplicate the base code.
        $base = strtoupper($data['currencyCode']);
        $extra = [];
        foreach ($data['currencies'] as $c) {
            $code = strtoupper($c['code']);
            if ($code !== $base) {
                $extra[] = ['code' => $code, 'symbol' => $c['symbol'], 'rate' => (float) $c['rate']];
            }
        }

        $settings->setMany([
            'localization.country' => $data['country'],
            // Persist the ISO code with the symbol so displayed currency and charged
            // currency can't drift (SET-03).
            'localization.currency_code' => $base,
            'localization.currency_symbol' => $data['currencySymbol'],
            'localization.currency_position' => $data['currencyPosition'],
            'localization.currency_decimals' => $data['currencyDecimals'],
            'localization.currencies' => $extra,
            'localization.timezone' => $data['timezone'],
            'localization.date_format' => $data['dateFormat'],
            'localization.weight_unit' => $data['weightUnit'],
            'localization.language' => $data['language'],
            'localization.text_direction' => $data['textDirection'],
        ]);

        $this->dispatch('toast', message: 'Localization settings saved', type: 'success');
    }

    public function render()
    {
        return View::make('settings::livewire.localization-settings', [
            'countries' => config('presets.countries'),
            'timezones' => \DateTimeZone::listIdentifiers(),
            'languages' => config('shopcrafty.available_locales', []),
        ]);
    }
}
