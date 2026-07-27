<?php

namespace Themicly\Shopcrafty\Modules\Settings\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Themicly\Shopcrafty\Modules\Settings\Services\DemoImporter;

/**
 * One-click demo import from the admin. Lets the owner drop a ready-made,
 * theme-matched catalogue into the store (or add another pack) without the CLI.
 */
class DemoManager extends Component
{
    /** Pack currently importing (for the button spinner). */
    #[Locked]
    public ?string $importing = null;

    public function import(string $pack, DemoImporter $importer): void
    {
        if (! $importer->has($pack)) {
            $this->dispatch('toast', message: 'Unknown demo pack.', type: 'danger');

            return;
        }

        $this->importing = $pack;
        $importer->import($pack);
        $this->importing = null;

        $this->dispatch('toast', message: 'Demo content imported — visit your storefront.', type: 'success');
    }

    public function render()
    {
        return View::make('settings::livewire.demo-manager', [
            'packs' => app(DemoImporter::class)->packs(),
        ]);
    }
}
