<?php

namespace Themicly\Shopcrafty\Modules\Reports\Concerns;

use Illuminate\Support\Carbon;
use Livewire\Attributes\Url;

/**
 * The 7/30/90-day + custom-range picker shared by every focused report page
 * (Orders, Inventory, Customers, Coupons, Refunds), extracted from
 * ReportsOverview so the same URL-shareable, preset-aware behavior — a custom
 * date pair wins over the preset unless it happens to match one — stays
 * identical everywhere instead of drifting per page.
 */
trait HasDateRange
{
    /** Preset length in days; 0 means a custom From/To range. */
    #[Url]
    public int $range = 30;

    #[Url(as: 'from')]
    public string $fromDate = '';

    #[Url(as: 'to')]
    public string $toDate = '';

    public function bootHasDateRange(): void
    {
        if ($this->validDates() && ! $this->datesMatchPreset()) {
            $this->range = 0;
        } else {
            $this->syncDatesToPreset();
        }
    }

    protected function validDates(): bool
    {
        try {
            Carbon::createFromFormat('Y-m-d', $this->fromDate);
            Carbon::createFromFormat('Y-m-d', $this->toDate);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    protected function datesMatchPreset(): bool
    {
        return $this->range > 0
            && $this->fromDate === now()->subDays($this->range - 1)->toDateString()
            && $this->toDate === now()->toDateString();
    }

    /** Presets keep the date inputs in sync so switching to custom starts from them. */
    protected function syncDatesToPreset(): void
    {
        if ($this->range <= 0) {
            $this->range = 30;
        }

        $this->fromDate = now()->subDays($this->range - 1)->toDateString();
        $this->toDate = now()->toDateString();
    }

    /**
     * The active window as [from, to]. Presets anchor on today; a custom range
     * spans the chosen dates in full days. Falls back to 30 days on bad input.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function period(): array
    {
        if ($this->range > 0) {
            return [now()->subDays($this->range - 1)->startOfDay(), now()];
        }

        try {
            $from = Carbon::createFromFormat('Y-m-d', $this->fromDate)->startOfDay();
            $to = Carbon::createFromFormat('Y-m-d', $this->toDate)->endOfDay();
        } catch (\Throwable) {
            return [now()->subDays(29)->startOfDay(), now()];
        }

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [$from, $to];
    }

    /** Days in the active window — sizes the "previous period" comparison. */
    protected function periodDays(Carbon $from, Carbon $to): int
    {
        return (int) $from->diffInDays($to) + 1;
    }

    protected function delta(int $current, int $previous): ?int
    {
        if ($previous === 0) {
            return null;
        }

        return (int) round(($current - $previous) / $previous * 100);
    }

    public function updatedFromDate(): void
    {
        $this->useCustomRange();
    }

    public function updatedToDate(): void
    {
        $this->useCustomRange();
    }

    protected function useCustomRange(): void
    {
        if ($this->validDates()) {
            $this->range = 0;
        } elseif ($this->range === 0) {
            $this->syncDatesToPreset();
        }
    }
}
