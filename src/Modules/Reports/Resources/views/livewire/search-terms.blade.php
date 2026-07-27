<div>
    {{-- Stat row --}}
    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-ui.stat-card label="Distinct terms" :value="number_format($distinctTerms)" />
        <x-ui.stat-card label="Total searches" :value="number_format($totalSearches)" />
        <x-ui.stat-card label="Top term" :value="$topTerm?->term ?? '—'" :hint="$topTerm ? number_format($topTerm->count).' searches' : null" />
    </div>

    {{-- Filters --}}
    <div class="mb-4 flex flex-wrap items-end gap-3">
        <div class="w-full max-w-xs">
            <x-ui.input wire:model.live.debounce.400ms="search" placeholder="Filter terms…" aria-label="Filter terms" />
        </div>
        <div class="w-40">
            <x-ui.select wire:model.live="sort" aria-label="Sort by">
                <option value="count">Most popular</option>
                <option value="recent">Most recent</option>
            </x-ui.select>
        </div>
        <div class="w-28">
            <x-ui.select wire:model.live="perPage" aria-label="Rows per page">
                <option value="25">25 / page</option>
                <option value="50">50 / page</option>
                <option value="100">100 / page</option>
            </x-ui.select>
        </div>
        <p class="ml-auto text-sm text-content-muted">Showing {{ $terms->total() }} term(s)</p>
    </div>

    @if ($terms->isEmpty())
        <div class="rounded-lg border border-line bg-surface-raised">
            <x-ui.empty-state icon="search" title="No search terms yet"
                description="Data appears here as soon as shoppers submit a search on your storefront. Each term is counted once per visitor session." />
        </div>
    @else
        <x-ui.table printable title="Search terms">
            <thead>
                <tr>
                    <th>Term</th>
                    <th class="!text-right">Searches</th>
                    <th>Last searched</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($terms as $term)
                    <tr wire:key="term-{{ $term->id }}">
                        <td class="font-medium text-content">{{ $term->term }}</td>
                        <td class="text-right">{{ number_format($term->count) }}</td>
                        <td>
                            @if ($term->last_searched_at)
                                <span title="{{ $term->last_searched_at->format('j M Y, H:i') }}">{{ $term->last_searched_at->diffForHumans() }}</span>
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>

        <div class="mt-5">{{ $terms->links() }}</div>
    @endif
</div>
