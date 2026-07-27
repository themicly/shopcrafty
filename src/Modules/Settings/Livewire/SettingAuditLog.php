<?php

namespace Themicly\Shopcrafty\Modules\Settings\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Themicly\Shopcrafty\Modules\Settings\Models\SettingAudit;

/**
 * Read-only list of recent config changes (key, who, when, old -> new).
 * Owner-only, like the rest of the settings area.
 */
class SettingAuditLog extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $audits = SettingAudit::query()
            ->when($this->search !== '', fn ($q) => $q->where(function ($w) {
                $w->where('key', 'like', "%{$this->search}%")
                    ->orWhere('user_name', 'like', "%{$this->search}%");
            }))
            ->latest('created_at')
            ->latest('id')
            ->paginate(25);

        return View::make('settings::livewire.setting-audit-log', [
            'audits' => $audits,
        ]);
    }
}
