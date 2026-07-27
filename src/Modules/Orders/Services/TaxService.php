<?php

namespace Themicly\Shopcrafty\Modules\Orders\Services;

/**
 * Computes tax on a taxable base (goods after discount). Supports exclusive tax
 * (added on top of prices) and inclusive tax (already baked into prices, shown
 * for information). Rate is a percentage; all amounts are minor units.
 */
class TaxService
{
    public function enabled(): bool
    {
        return (bool) settings('tax.enabled', false);
    }

    public function rate(): float
    {
        return (float) settings('tax.rate', 0);
    }

    public function inclusive(): bool
    {
        return (bool) settings('tax.inclusive', false);
    }

    public function label(): string
    {
        return (string) settings('tax.label', 'Tax');
    }

    /** The tax collected on a taxable base — the actual tax amount, inclusive or not. */
    public function taxFor(int $base): int
    {
        if (! $this->enabled() || $this->rate() <= 0 || $base <= 0) {
            return 0;
        }

        $rate = $this->rate();

        return $this->inclusive()
            ? (int) round($base * $rate / (100 + $rate)) // extract the tax portion of a tax-inclusive price
            : (int) round($base * $rate / 100);          // add tax on top
    }

    /** Amount added to the order total. Zero when inclusive (tax is already in the prices). */
    public function addedTaxFor(int $base): int
    {
        return $this->inclusive() ? 0 : $this->taxFor($base);
    }
}
