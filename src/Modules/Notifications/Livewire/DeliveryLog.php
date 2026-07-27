<?php

namespace Themicly\Shopcrafty\Modules\Notifications\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Livewire\WithPagination;
use Themicly\Shopcrafty\Modules\Notifications\Models\NotificationLog;

/**
 * Admin: the delivery audit trail — every attempted send, its gateway and
 * outcome. Filterable by channel and status.
 */
class DeliveryLog extends Component
{
    use WithPagination;

    public string $channel = '';

    public string $status = '';

    public function updating(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $logs = NotificationLog::query()
            ->when($this->channel !== '', fn ($q) => $q->where('channel', $this->channel))
            ->when($this->status !== '', fn ($q) => $q->where('status', $this->status))
            ->latest()
            ->paginate(20);

        return View::make('notifications::livewire.delivery-log', ['logs' => $logs]);
    }
}
