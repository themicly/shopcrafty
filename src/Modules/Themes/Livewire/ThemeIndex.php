<?php

namespace Themicly\Shopcrafty\Modules\Themes\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Themes\Models\Theme;
use Themicly\Shopcrafty\Modules\Themes\Services\ThemeService;

class ThemeIndex extends Component
{
    public function mount(ThemeService $themes): void
    {
        // Pick up theme packages added to themes/ after installation (idempotent:
        // refreshes manifests without touching the active flag or buyer settings).
        $themes->syncFromDisk();
    }

    public function activate(int $id, ThemeService $themes): void
    {
        $themes->activate(Theme::findOrFail($id));
        $this->dispatch('toast', message: 'Theme activated', type: 'success');
    }

    public function render(ThemeService $themes)
    {
        return View::make('themes::livewire.theme-index', [
            'themes' => Theme::orderBy('name')->get(),
            'metadata' => fn (string $slug) => $themes->metadata($slug),
        ]);
    }
}
