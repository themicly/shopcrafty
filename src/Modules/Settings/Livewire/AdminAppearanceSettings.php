<?php

namespace Themicly\Shopcrafty\Modules\Settings\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Settings\Services\Settings;

/**
 * Lets the store owner recolor the admin panel itself (sidebar, buttons,
 * active states) — separate from the storefront theme customizer. The admin
 * UI already renders entirely off --bz-* CSS custom properties (see
 * resources/css/app.css), so this just persists three tokens and lets the
 * layout (components/layouts/admin.blade.php) override them on every page.
 */
class AdminAppearanceSettings extends Component
{
    // Must match the :root defaults in resources/css/app.css exactly, so
    // "no customization yet" and "explicitly reset" both render identically.
    public const DEFAULT_PRIMARY = '#7c3aed';

    public const DEFAULT_PRIMARY_FG = '#ffffff';

    public const DEFAULT_BRAND_2 = '#d946ef';

    public string $primary = self::DEFAULT_PRIMARY;

    public string $primaryFg = self::DEFAULT_PRIMARY_FG;

    public string $brand2 = self::DEFAULT_BRAND_2;

    public function mount(Settings $settings): void
    {
        $this->primary = (string) $settings->get('admin_appearance.primary', self::DEFAULT_PRIMARY);
        $this->primaryFg = (string) $settings->get('admin_appearance.primary_fg', self::DEFAULT_PRIMARY_FG);
        $this->brand2 = (string) $settings->get('admin_appearance.brand_2', self::DEFAULT_BRAND_2);
    }

    /** Resets the in-editor draft only — click Save to persist and publish it. */
    public function resetToDefault(): void
    {
        $this->primary = self::DEFAULT_PRIMARY;
        $this->primaryFg = self::DEFAULT_PRIMARY_FG;
        $this->brand2 = self::DEFAULT_BRAND_2;
    }

    public function save(Settings $settings): void
    {
        $data = $this->validate([
            'primary' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'primaryFg' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'brand2' => ['required', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        $settings->setMany([
            'admin_appearance.primary' => $data['primary'],
            'admin_appearance.primary_fg' => $data['primaryFg'],
            'admin_appearance.brand_2' => $data['brand2'],
        ]);

        $this->dispatch('toast', message: 'Appearance saved', type: 'success');
    }

    public function render()
    {
        return View::make('settings::livewire.admin-appearance-settings');
    }
}
