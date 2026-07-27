<?php

namespace Themicly\Shopcrafty\Modules\Settings\Services;

/**
 * Display-currency conversion for the storefront. Money is always stored and
 * charged in the store's base currency (minor units); this converts amounts for
 * display when a visitor picks another currency. Admin screens always show base.
 */
class CurrencyService
{
    /** @var array<string, array{code:string,symbol:string,rate:float,decimals:int,position:string}>|null */
    protected ?array $cache = null;

    public function baseCode(): string
    {
        return strtoupper((string) settings('localization.currency_code', 'USD'));
    }

    /**
     * All configured currencies keyed by code — the base (rate 1) plus any extras
     * defined in settings. Malformed extras are ignored.
     *
     * @return array<string, array{code:string,symbol:string,rate:float,decimals:int,position:string}>
     */
    public function currencies(): array
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $base = [
            'code' => $this->baseCode(),
            'symbol' => (string) settings('localization.currency_symbol', '$'),
            'rate' => 1.0,
            'decimals' => (int) settings('localization.currency_decimals', 2),
            'position' => (string) settings('localization.currency_position', 'before'),
        ];

        $list = [$base['code'] => $base];

        foreach ((array) settings('localization.currencies', []) as $row) {
            $code = strtoupper((string) ($row['code'] ?? ''));
            $rate = (float) ($row['rate'] ?? 0);

            if (strlen($code) !== 3 || $code === $base['code'] || $rate <= 0) {
                continue;
            }

            $list[$code] = [
                'code' => $code,
                'symbol' => (string) ($row['symbol'] ?? $code),
                'rate' => $rate,
                'decimals' => isset($row['decimals']) ? (int) $row['decimals'] : $base['decimals'],
                'position' => ($row['position'] ?? $base['position']) === 'after' ? 'after' : 'before',
            ];
        }

        return $this->cache = $list;
    }

    public function hasMultiple(): bool
    {
        return count($this->currencies()) > 1;
    }

    /** The visitor's chosen display currency — storefront only, else base. */
    public function activeCode(): string
    {
        $base = $this->baseCode();

        // Admin always shows base so reports/orders never drift from what's charged.
        if (request()->is('admin', 'admin/*')) {
            return $base;
        }

        $selected = strtoupper((string) session('storefront_currency', ''));

        return isset($this->currencies()[$selected]) ? $selected : $base;
    }

    public function setActive(string $code): bool
    {
        $code = strtoupper($code);

        if (! isset($this->currencies()[$code])) {
            return false;
        }

        session(['storefront_currency' => $code]);

        return true;
    }

    /**
     * Base-currency minor amount → major units (e.g. cents → dollars). For
     * conversion-tracking payloads (GA4/FB Pixel), which must report the
     * actual charged amount — orders are always placed and charged in the
     * store's base currency, never the shopper's display currency.
     */
    public function toBaseMajor(int $minorBase): float
    {
        return round($minorBase / (10 ** $this->currencies()[$this->baseCode()]['decimals']), 2);
    }

    /** Format a base-currency minor amount in the active display currency. */
    public function format(int $minorBase, ?string $symbolOverride = null): string
    {
        $currencies = $this->currencies();
        $base = $currencies[$this->baseCode()];
        $active = $currencies[$this->activeCode()] ?? $base;

        // Convert base minor → base major → active major via the manual rate.
        $baseMajor = $minorBase / (10 ** $base['decimals']);
        $amount = $baseMajor * $active['rate'];

        $symbol = $symbolOverride ?? $active['symbol'];
        $formatted = number_format($amount, $active['decimals']);

        return $active['position'] === 'after' ? "{$formatted}{$symbol}" : "{$symbol}{$formatted}";
    }
}
