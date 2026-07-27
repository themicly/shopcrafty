<?php

namespace Themicly\Shopcrafty\Modules\Marketing\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Component;
use Livewire\WithPagination;
use Themicly\Shopcrafty\Modules\Marketing\Models\NewsletterSubscriber;

class NewsletterSubscribers extends Component
{
    use WithPagination;

    public string $filter = 'all';

    public function updatingFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $subscribers = NewsletterSubscriber::query()
            ->when($this->filter !== 'all', fn ($q) => $q->where('status', $this->filter))
            ->latest()
            ->paginate(20);

        return View::make('marketing::livewire.newsletter-subscribers', [
            'subscribers' => $subscribers,
            'subscribedCount' => NewsletterSubscriber::subscribed()->count(),
            'totalCount' => NewsletterSubscriber::count(),
        ]);
    }
}
