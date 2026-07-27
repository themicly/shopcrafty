<?php

namespace Themicly\Shopcrafty\Modules\Reports\Livewire;

use Illuminate\Support\Facades\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Themicly\Shopcrafty\Modules\Catalog\Models\SearchTerm;

/**
 * Admin "Search terms" report — what shoppers type into storefront search,
 * aggregated by SearchTermRecorder (submitted searches only, session-deduped).
 */
class SearchTerms extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    /** 'count' (most popular, default) or 'recent' (last searched). */
    #[Url]
    public string $sort = 'count';

    #[Url]
    public int $perPage = 25;

    /** Search analytics sit with the other reports — owner-only (manage-config). */
    public function mount(): void
    {
        abort_unless(auth()->user()?->can('manage-config'), 403);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $terms = SearchTerm::query()
            ->when($this->search !== '', fn ($q) => $q->where('term', 'like', "%{$this->search}%"))
            ->when(
                $this->sort === 'recent',
                fn ($q) => $q->orderByDesc('last_searched_at'),
                fn ($q) => $q->orderByDesc('count')->orderByDesc('last_searched_at'),
            )
            ->paginate($this->perPage);

        return View::make('reports::livewire.search-terms', [
            'terms' => $terms,
            'distinctTerms' => SearchTerm::count(),
            'totalSearches' => (int) SearchTerm::sum('count'),
            'topTerm' => SearchTerm::orderByDesc('count')->first(),
        ]);
    }
}
