@props(['key'])

@php
    $navigation = app(\Themicly\Shopcrafty\Core\Navigation\NavigationRegistry::class);
    $items = collect($navigation->indexMenu($key))
        ->filter(fn (array $item) => ! ($item['gate'] ?? null) || \Illuminate\Support\Facades\Gate::allows($item['gate']))
        ->filter(fn (array $item) => ! empty($item['route']) && \Illuminate\Support\Facades\Route::has($item['route']));
@endphp

@if ($items->isNotEmpty())
    <div class="mb-4 flex flex-wrap items-center justify-end gap-2">
        @foreach ($items as $item)
            <x-ui.button size="sm" variant="secondary" :href="route($item['route'])">
                {{ $item['label'] }}
            </x-ui.button>
        @endforeach
    </div>
@endif
