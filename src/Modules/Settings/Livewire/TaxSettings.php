<?php

namespace Themicly\Shopcrafty\Modules\Settings\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Settings\Services\Settings;

class TaxSettings extends Component
{
    public bool $enabled = false;

    public string $label = 'Tax';

    public string $rate = '0';

    public bool $inclusive = false;

    public function mount(Settings $settings): void
    {
        $this->enabled = (bool) $settings->get('tax.enabled', false);
        $this->label = (string) $settings->get('tax.label', 'Tax');
        $this->rate = (string) $settings->get('tax.rate', '0');
        $this->inclusive = (bool) $settings->get('tax.inclusive', false);
    }

    public function save(Settings $settings): void
    {
        $data = $this->validate([
            'enabled' => ['boolean'],
            'label' => ['required', 'string', 'max:40'],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'inclusive' => ['boolean'],
        ]);

        $settings->setMany([
            'tax.enabled' => $data['enabled'],
            'tax.label' => $data['label'],
            'tax.rate' => (float) $data['rate'],
            'tax.inclusive' => $data['inclusive'],
        ]);

        $this->dispatch('toast', message: 'Tax settings saved', type: 'success');
    }

    public function render()
    {
        return View::make('settings::livewire.tax-settings');
    }
}
