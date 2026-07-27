<?php

namespace Themicly\Shopcrafty\Modules\Settings\Livewire;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Models\User;
use Themicly\Shopcrafty\Modules\Orders\Models\ShippingZone;
use Themicly\Shopcrafty\Modules\Settings\Actions\ApplyCountryPreset;
use Themicly\Shopcrafty\Modules\Settings\Services\DemoImporter;
use Themicly\Shopcrafty\Modules\Settings\Services\InstallerService;
use Themicly\Shopcrafty\Modules\Settings\Services\Settings;
use Themicly\Shopcrafty\Modules\Themes\Models\Theme;
use Themicly\Shopcrafty\Modules\Themes\Services\ThemeService;

/**
 * The web installer. Walks a non-technical buyer from a fresh upload to a live,
 * demo-filled store — requirements → database → admin → store → demo → done —
 * with no shell. Each step validates before advancing; the final step writes
 * the install lock so the wizard can never run again.
 */
class Installer extends Component
{
    public int $step = 1;

    // Database (only needed when the app can't already connect).
    public string $dbHost = '';

    public string $dbPort = '3306';

    public string $dbName = '';

    public string $dbUser = '';

    public string $dbPass = '';

    public bool $dbConnected = false;

    public ?string $dbError = null;

    // Admin account.
    public string $adminName = '';

    public string $adminEmail = '';

    public string $adminPassword = '';

    // Store setup.
    public string $storeName = '';

    public string $country = '';

    // Demo import ('' = skip).
    public string $demoPack = '';

    public function mount(): void
    {
        if (InstallerService::installed()) {
            $this->redirect('/');

            return;
        }

        $this->dbHost = (string) config('database.connections.mysql.host', '127.0.0.1');
        $this->dbPort = (string) config('database.connections.mysql.port', '3306');
        $this->dbName = (string) config('database.connections.mysql.database', '');
        $this->dbUser = (string) config('database.connections.mysql.username', '');
        $this->storeName = (string) config('app.name', 'Shopcrafty');
        $this->country = (string) config('presets.default', 'generic');
        $this->dbConnected = $this->canConnect();
    }

    private function canConnect(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /** Step 2: test the entered credentials and, if good, persist them to .env. */
    public function testDatabase(InstallerService $installer): void
    {
        $result = $installer->testDatabase($this->dbHost, $this->dbPort, $this->dbName, $this->dbUser, $this->dbPass);

        $this->dbConnected = $result['ok'];
        $this->dbError = $result['ok'] ? null : $result['message'];

        if ($result['ok']) {
            $installer->writeEnv([
                'DB_CONNECTION' => 'mysql',
                'DB_HOST' => $this->dbHost,
                'DB_PORT' => $this->dbPort,
                'DB_DATABASE' => $this->dbName,
                'DB_USERNAME' => $this->dbUser,
                'DB_PASSWORD' => $this->dbPass,
            ]);
        }
    }

    public function next(InstallerService $installer): void
    {
        match ($this->step) {
            1 => $this->leaveRequirements($installer),
            2 => $this->leaveDatabase(),
            3 => $this->leaveAdmin(),
            4 => $this->leaveStore(),
            5 => $this->leaveDemo(),
            default => null,
        };
    }

    public function back(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    private function leaveRequirements(InstallerService $installer): void
    {
        if (! $installer->requirementsPass()) {
            $this->addError('requirements', 'Please resolve the failing requirements before continuing.');

            return;
        }

        $this->step = 2;
    }

    private function leaveDatabase(): void
    {
        if (! $this->dbConnected) {
            $this->addError('db', 'Test the database connection before continuing.');

            return;
        }

        // Build the schema (idempotent — a no-op if already migrated) and make
        // sure the app has an encryption key.
        if (empty(config('app.key'))) {
            Artisan::call('key:generate', ['--force' => true]);
        }

        Artisan::call('migrate', ['--force' => true]);

        $this->step = 3;
    }

    private function leaveAdmin(): void
    {
        $this->validate([
            'adminName' => ['required', 'string', 'max:120'],
            'adminEmail' => ['required', 'email', 'max:190'],
            'adminPassword' => ['required', 'string', 'min:8'],
        ]);

        User::updateOrCreate(
            ['email' => $this->adminEmail],
            [
                'name' => $this->adminName,
                'password' => $this->adminPassword,
                'role' => User::ROLE_OWNER,
                'status' => 'active',
            ],
        );

        $this->step = 4;
    }

    private function leaveStore(): void
    {
        $this->validate([
            'storeName' => ['required', 'string', 'max:120'],
            'country' => ['required', 'string', 'in:'.implode(',', array_keys(config('presets.countries', [])))],
        ]);

        app(ApplyCountryPreset::class)->handle($this->country);

        $settings = app(Settings::class);
        $settings->setMany(['general.store_name' => $this->storeName]);

        // Register bundled themes + activate a default so the storefront renders.
        $themes = app(ThemeService::class);
        $themes->syncFromDisk();

        if (! Theme::where('is_active', true)->exists() && ($default = Theme::where('slug', 'default')->first())) {
            $themes->activate($default);
        }

        if (ShippingZone::count() === 0) {
            ShippingZone::create(['name' => 'Standard shipping', 'rate' => 500, 'free_above' => 5000, 'position' => 0]);
            ShippingZone::create(['name' => 'Express shipping', 'rate' => 1200, 'free_above' => null, 'position' => 1]);
        }

        $this->step = 5;
    }

    private function leaveDemo(): void
    {
        $importer = app(DemoImporter::class);

        if ($this->demoPack !== '' && $importer->has($this->demoPack)) {
            $importer->import($this->demoPack);
        }

        $this->step = 6;
    }

    public function finish(InstallerService $installer): void
    {
        $installer->markInstalled();

        // Stamp the shipped version so the upgrade flow knows where we started.
        $installer->stampVersion();

        $this->redirect(route('login'));
    }

    public function render(InstallerService $installer)
    {
        return View::make('settings::livewire.installer', [
            'requirements' => $installer->requirements(),
            'requirementsPass' => $installer->requirementsPass(),
            'presets' => config('presets.countries', []),
            'packs' => app(DemoImporter::class)->packs(),
        ])->layout('install.layout');
    }
}
