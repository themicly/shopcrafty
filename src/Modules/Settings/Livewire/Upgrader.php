<?php

namespace Themicly\Shopcrafty\Modules\Settings\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Settings\Services\InstallerService;

/**
 * The post-upload upgrade screen. After a buyer re-uploads new files over FTP,
 * an owner visits /install/upgrade to apply any pending migrations without
 * shell access. The route is gated to authenticated owners (see routes/web.php)
 * and only meaningful on an already-installed store — this never re-runs the
 * installer and never touches data beyond the migrations themselves.
 */
class Upgrader extends Component
{
    public ?string $result = null;

    public bool $success = false;

    public bool $done = false;

    public function mount(): void
    {
        // The upgrade flow only makes sense once the store is installed. A fresh,
        // un-installed upload belongs in the wizard.
        if (! InstallerService::installed()) {
            $this->redirect(route('install.index'));
        }
    }

    /** Run migrations, clear caches, and stamp the new version. */
    public function upgrade(InstallerService $installer): void
    {
        $outcome = $installer->runUpgrade();

        $this->success = $outcome['ok'];
        $this->result = $outcome['message'];
        $this->done = true;
    }

    public function render(InstallerService $installer)
    {
        return View::make('settings::livewire.upgrader', [
            'currentVersion' => $installer->installedVersion(),
            'targetVersion' => $installer->appVersion(),
            'pending' => $installer->pendingMigrations(),
            'upgradeAvailable' => $installer->upgradeAvailable(),
        ])->layout('install.layout');
    }
}
