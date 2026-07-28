@php
    use Illuminate\Support\Facades\Gate;
    use Illuminate\Support\Facades\Route;
    $navigation = app(\Themicly\Shopcrafty\Core\Navigation\NavigationRegistry::class);
    $addons = app(\Themicly\Shopcrafty\Core\Module\AddonRegistry::class);

    $resolve = function (array $item) {
        $href = ($item['route'] ?? null) && Route::has($item['route']) ? route($item['route']) : '#';
        $patterns = (array) ($item['match'] ?? $item['route'] ?? []);
        $active = $patterns && request()->routeIs(...$patterns);
        return [$href, $active];
    };

    // Live nav badges (kept cheap — one small count per admin page load).
    $badge = fn (?string $key) => match ($key) {
        'cod' => \Themicly\Shopcrafty\Modules\Orders\Models\Order::where('cod_verification_status', 'unverified')->where('status', 'pending')->count(),
        default => 0,
    };

    // An item is visible when it passes its permission gate AND, if it names a
    // `setting` key, that store-config flag is on (defaults true, so items
    // without one are unaffected) — e.g. Reviews only shows once enabled.
    $visible = fn (array $i) => (! ($i['gate'] ?? null) || Gate::allows($i['gate']))
        && (! ($i['setting'] ?? null) || settings($i['setting'], true))
        && (! ($i['addon'] ?? null) || $addons->installed($i['addon']));
@endphp

{{--
    Width, labels, and centering are driven by [data-sidebar] on <html> via CSS
    (see app.css / .bz-sidebar), set before paint — so there is no width flash
    on navigation. Alpine only handles the mobile off-canvas open/close.
--}}
<aside
    class="bz-sidebar fixed inset-y-0 left-0 z-40 flex -translate-x-full flex-col border-r border-line bg-surface-raised transition-[width,transform] duration-200 lg:sticky lg:top-0 lg:z-auto lg:h-screen lg:translate-x-0"
    :class="sidebarOpen && '!translate-x-0'"
>
    {{-- Brand --}}
    <div class="bz-sidebar-brand flex h-14 items-center gap-2.5 border-b border-line px-4">
        @if ($logo = settings('general.logo'))
            {{-- The logo is the brand — no need to also spell out the store name beside it. --}}
            <img src="{{ $logo }}" alt="{{ settings('general.store_name', config('app.name', 'Shopcrafty')) }}" class="h-8 w-auto max-w-[9rem] shrink-0 object-contain">
        @else
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg font-bold text-white shadow-sm"
                style="background: linear-gradient(135deg, var(--bz-primary), var(--bz-brand-2))">{{ strtoupper(substr(settings('general.store_name', config('app.name', 'Shopcrafty')), 0, 1)) }}</span>
            <span class="bz-sidebar-label text-base font-semibold text-content">{{ settings('general.store_name', config('app.name', 'Shopcrafty')) }}</span>
        @endif
    </div>

    {{-- Primary nav (grouped into sections) --}}
    <nav class="flex-1 overflow-y-auto p-3">
        @foreach ($navigation->main() as $group)
            @php $groupItems = array_filter($group['items'], $visible); @endphp
            @continue(empty($groupItems))
            <p class="bz-sidebar-label px-3 pb-1 pt-4 text-[11px] font-semibold uppercase tracking-widest text-content-muted first:pt-1">{{ $group['label'] }}</p>
            <div class="space-y-0.5">
                @foreach ($groupItems as $item)
                    @php [$href, $active] = $resolve($item); $count = $badge($item['badge'] ?? null); @endphp
                    <a
                        href="{{ $href }}"
                        title="{{ $item['label'] }}"
                        @class([
                            'bz-sidebar-link group relative flex h-9 items-center gap-3 rounded-lg px-3 text-sm transition-colors',
                            'bg-primary-soft font-medium text-primary' => $active,
                            'text-content-secondary hover:bg-surface-sunken hover:text-content' => ! $active,
                        ])
                    >
                        @if ($active)
                            <span class="absolute -left-3 top-1/2 h-5 w-1 -translate-y-1/2 rounded-r-full bg-primary"></span>
                        @endif
                        <x-ui.icon :name="$item['icon']" class="h-5 w-5 shrink-0 {{ $active ? '' : 'transition-colors group-hover:text-primary' }}" />
                        <span class="bz-sidebar-label truncate">{{ $item['label'] }}</span>
                        @if ($count > 0)
                            <span class="bz-sidebar-label ml-auto rounded-full bg-warning-soft px-1.5 text-[11px] font-semibold tabular-nums text-warning">{{ $count }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        @endforeach
    </nav>

    {{-- Footer nav --}}
    <div class="space-y-0.5 border-t border-line p-3">
        @foreach ($navigation->footer() as $item)
            @continue(($item['gate'] ?? null) && Gate::denies($item['gate']))
            @continue(($item['addon'] ?? null) && ! $addons->installed($item['addon']))
            @php [$href, $active] = $resolve($item); @endphp
            <a
                href="{{ $href }}"
                title="{{ $item['label'] }}"
                @class([
                    'bz-sidebar-link group relative flex h-9 items-center gap-3 rounded-lg px-3 text-sm transition-colors',
                    'bg-primary-soft font-medium text-primary' => $active,
                    'text-content-secondary hover:bg-surface-sunken hover:text-content' => ! $active,
                ])
            >
                @if ($active)
                    <span class="absolute -left-3 top-1/2 h-5 w-1 -translate-y-1/2 rounded-r-full bg-primary"></span>
                @endif
                <x-ui.icon :name="$item['icon']" class="h-5 w-5 shrink-0 {{ $active ? '' : 'transition-colors group-hover:text-primary' }}" />
                <span class="bz-sidebar-label truncate">{{ $item['label'] }}</span>
            </a>
        @endforeach
        <x-admin.brand-footer />
    </div>
</aside>
