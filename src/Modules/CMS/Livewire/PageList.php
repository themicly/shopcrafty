<?php

namespace Themicly\Shopcrafty\Modules\CMS\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Themicly\Shopcrafty\Modules\CMS\Models\Page;

class PageList extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $statusFilter = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        Page::whereKey($id)->delete();
        $this->dispatch('toast', message: 'Page deleted', type: 'success');
    }

    public function render()
    {
        return View::make('cms::livewire.page-list', [
            'pages' => Page::query()
                ->when($this->search !== '', fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
                ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
                ->latest()
                ->paginate(15),
        ]);
    }
}
