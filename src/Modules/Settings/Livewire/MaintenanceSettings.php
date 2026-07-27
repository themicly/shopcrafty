<?php

namespace Themicly\Shopcrafty\Modules\Settings\Livewire;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Settings\Services\Settings;

class MaintenanceSettings extends Component
{
    public bool $maintenanceEnabled = false;

    public string $maintenanceMessage = '';

    public string $maintenancePasscode = '';

    public function mount(Settings $settings): void
    {
        $this->maintenanceEnabled = (bool) $settings->get('maintenance.enabled', false);
        $this->maintenanceMessage = (string) $settings->get('maintenance.message', "We'll be back shortly.");
        $this->maintenancePasscode = (string) $settings->get('maintenance.passcode', '');
    }

    public function saveMaintenance(Settings $settings): void
    {
        $this->validate([
            'maintenanceEnabled' => ['boolean'],
            'maintenanceMessage' => ['nullable', 'string', 'max:500'],
            'maintenancePasscode' => ['nullable', 'string', 'max:64'],
        ]);

        $settings->setMany([
            'maintenance.enabled' => $this->maintenanceEnabled,
            'maintenance.message' => $this->maintenanceMessage,
            'maintenance.passcode' => $this->maintenancePasscode,
        ]);

        $this->dispatch('toast', message: 'Maintenance settings saved', type: 'success');
    }

    public function clearCache(): void
    {
        Artisan::call('cache:clear');
        Artisan::call('view:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        app(Settings::class)->flush();

        $this->dispatch('toast', message: 'Application caches cleared', type: 'success');
    }

    public function render()
    {
        return View::make('settings::livewire.maintenance-settings', [
            'cacheDriver' => config('cache.default'),
            'queueDriver' => config('queue.default'),
        ]);
    }
}
