@props([
    'title' => null,
    'subtitle' => null,
    'nav' => [],
])

@php
    use Illuminate\Support\Facades\Gate;

    // Accept either a flat item list or labeled groups ([['label' => …,
    // 'items' => […]], …]); flat lists become a single unlabeled group.
    $groups = (isset($nav[0]) && array_key_exists('items', $nav[0]))
        ? $nav
        : [['label' => null, 'items' => $nav]];

    $isActive = function (array $item): bool {
        if (! empty($item['match']) && request()->routeIs(...(array) $item['match'])) {
            return true;
        }
        $active = request()->routeIs($item['route']);
        foreach ($item['params'] ?? [] as $k => $v) {
            $active = $active && (string) request()->route($k) === (string) $v;
        }

        return $active;
    };
@endphp

{{--
    Second-sidebar section layout: a contained, grouped sub-nav on the left
    (horizontally scrollable chip row on mobile) and the active screen on the
    right. Items may carry a `desc` note, `params`, and a `match` route-pattern
    list for active-state detection across parameterized routes.
--}}
<div>
    @if ($title)
        <div class="mb-6">
            <h2 class="text-xl font-semibold text-content">{{ $title }}</h2>
            @if ($subtitle)
                <p class="mt-1 text-sm text-content-muted">{{ $subtitle }}</p>
            @endif
        </div>
    @endif

    <div class="flex flex-col gap-6 lg:flex-row">
        <aside class="lg:w-64 lg:shrink-0">
            {{-- top-[4.5rem]/max-h-[calc(100vh-5.5rem)]: the admin topbar is a
                 sticky h-14 (3.5rem) bar of its own, so this must stick below
                 it (not top-0) and cap its total height to the viewport minus
                 that offset plus a bottom margin — otherwise it renders taller
                 than the space actually left below the topbar, and it's the
                 whole page that ends up scrolling instead of just the nav.
                 The cap is on this wrapper (flex column), with the nav as the
                 flexed, independently-scrolling child, so an optional
                 asideTop (e.g. the settings search box) is budgeted for too
                 instead of pushing the nav past the same fixed height. --}}
            <div class="lg:sticky lg:top-[4.5rem] lg:flex lg:max-h-[calc(100vh_-_5.5rem)] lg:flex-col">
            @isset($asideTop)
                <div class="mb-3 lg:shrink-0">{{ $asideTop }}</div>
            @endisset
            <nav class="flex gap-1 overflow-x-auto pb-1 lg:block lg:min-h-0 lg:flex-1 lg:space-y-4 lg:overflow-y-auto lg:overscroll-contain lg:rounded-xl lg:border lg:border-line lg:bg-surface-raised lg:p-2 lg:pb-3"
                aria-label="{{ $title ?? 'Section' }} navigation">
                @foreach ($groups as $group)
                    @php
                        $items = collect($group['items'])->reject(fn ($item) => ($item['gate'] ?? null) && Gate::denies($item['gate']));
                    @endphp
                    @continue($items->isEmpty())

                    <div class="contents lg:block">
                        @if (! empty($group['label']))
                            <p class="hidden px-3 pb-1 pt-2 text-[11px] font-semibold uppercase tracking-wider text-content-muted lg:block">{{ $group['label'] }}</p>
                        @endif

                        @foreach ($items as $item)
                            @php $active = $isActive($item); @endphp
                            <a
                                href="{{ route($item['route'], $item['params'] ?? []) }}"
                                @class([
                                    'group relative shrink-0 rounded-lg px-3 py-2 text-sm transition-colors lg:block lg:shrink',
                                    'bg-primary-soft font-medium text-primary' => $active,
                                    'text-content-secondary hover:bg-surface-sunken hover:text-content' => ! $active,
                                ])
                                @if ($active) aria-current="page" @endif
                            >
                                <span class="block whitespace-nowrap lg:whitespace-normal">{{ $item['label'] }}</span>
                                @if (! empty($item['desc']))
                                    <span class="mt-0.5 hidden text-xs lg:block {{ $active ? 'text-primary/70' : 'text-content-muted' }}">{{ $item['desc'] }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </nav>
            </div>
        </aside>

        <div class="min-w-0 flex-1">
            {{ $slot }}
        </div>
    </div>
</div>
