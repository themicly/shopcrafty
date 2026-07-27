<?php

namespace Themicly\Shopcrafty\Modules\Settings\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Orders\Models\ShippingZone;

/**
 * Admin CRUD for shipping zones (previously seed-only). Rates are entered in
 * major units and stored as minor, consistent with the rest of the money model.
 */
class ShippingZones extends Component
{
    public bool $showForm = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $rate = '';

    public string $freeAbove = '';

    public bool $isActive = true;

    protected function decimals(): int
    {
        return (int) settings('localization.currency_decimals', 2);
    }

    protected function toMinor(string $value): int
    {
        return (int) round(((float) $value) * (10 ** $this->decimals()));
    }

    protected function toMajor(int $minor): string
    {
        return number_format($minor / (10 ** $this->decimals()), $this->decimals(), '.', '');
    }

    public function create(): void
    {
        $this->reset('editingId', 'name', 'rate', 'freeAbove');
        $this->isActive = true;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $zone = ShippingZone::findOrFail($id);
        $this->editingId = $zone->id;
        $this->name = $zone->name;
        $this->rate = $this->toMajor((int) $zone->rate);
        $this->freeAbove = $zone->free_above !== null ? $this->toMajor((int) $zone->free_above) : '';
        $this->isActive = (bool) $zone->is_active;
        $this->resetValidation();
        $this->showForm = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'rate' => ['required', 'numeric', 'min:0'],
            'freeAbove' => ['nullable', 'numeric', 'min:0'],
            'isActive' => ['boolean'],
        ]);

        $attributes = [
            'name' => $data['name'],
            'rate' => $this->toMinor($data['rate']),
            'free_above' => $data['freeAbove'] !== '' ? $this->toMinor($data['freeAbove']) : null,
            'is_active' => $data['isActive'],
        ];

        if ($this->editingId) {
            ShippingZone::findOrFail($this->editingId)->update($attributes);
            $message = 'Zone updated';
        } else {
            $attributes['position'] = (int) ShippingZone::max('position') + 1;
            ShippingZone::create($attributes);
            $message = 'Zone created';
        }

        $this->showForm = false;
        $this->dispatch('toast', message: $message, type: 'success');
    }

    public function toggle(int $id): void
    {
        $zone = ShippingZone::findOrFail($id);
        $zone->update(['is_active' => ! $zone->is_active]);
        $this->dispatch('toast', message: 'Zone updated', type: 'success');
    }

    public function delete(int $id): void
    {
        ShippingZone::findOrFail($id)->delete();
        $this->dispatch('toast', message: 'Zone deleted', type: 'success');
    }

    public function move(int $id, string $dir): void
    {
        $zone = ShippingZone::findOrFail($id);

        $swap = ShippingZone::when($dir === 'up',
            fn ($q) => $q->where('position', '<', $zone->position)->orderByDesc('position'),
            fn ($q) => $q->where('position', '>', $zone->position)->orderBy('position'),
        )->first();

        if ($swap) {
            [$zone->position, $swap->position] = [$swap->position, $zone->position];
            $zone->save();
            $swap->save();
        }
    }

    public function render()
    {
        return View::make('settings::livewire.shipping-zones', [
            'zones' => ShippingZone::orderBy('position')->get(),
            'symbol' => (string) settings('localization.currency_symbol', '$'),
        ]);
    }
}
