@php
    use Illuminate\Support\Facades\Gate;
    use Illuminate\Support\Facades\Route;
    $navigation = app(\Themicly\Shopcrafty\Core\Navigation\NavigationRegistry::class);

    $commands = collect();

    // Only surface a command the current user is actually allowed to reach. The
    // sidebar honours each item's `gate`; the palette must too, otherwise staff
    // see (and 403 on) owner-only screens like Settings/Reports/Notifications.
    $allowed = fn (array $item) => empty($item['gate']) || Gate::allows($item['gate']);

    $navItems = collect($navigation->main())->flatMap(fn ($group) => $group['items'] ?? [])
        ->merge($navigation->footer());

    foreach ($navItems as $item) {
        if (! $allowed($item)) {
            continue;
        }

        $commands->push([
            'group' => 'Navigate',
            'label' => $item['label'],
            'href' => ($item['route'] ?? null) && Route::has($item['route']) ? route($item['route']) : '#',
            'action' => null,
        ]);
    }

    foreach ($navigation->quickCreate() as $action) {
        if (! $allowed($action)) {
            continue;
        }

        $commands->push([
            'group' => 'Create',
            'label' => $action['label'],
            'href' => Route::has($action['route']) ? route($action['route']) : '#',
            'action' => null,
        ]);
    }

    $commands->push([
        'group' => 'Appearance',
        'label' => 'Toggle dark mode',
        'href' => null,
        'action' => 'toggle-theme',
    ]);
@endphp

<div
    x-data="commandPalette(@js($commands->values()), @js(Route::has('admin.search') ? route('admin.search') : null))"
    @open-command-palette.window="open()"
    @keydown.window="onKeydown($event)"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50"
    style="display: none;"
>
    {{-- Backdrop --}}
    <div
        x-show="show"
        x-transition.opacity
        class="fixed inset-0 bg-black/50"
        @click="close()"
    ></div>

    {{-- Panel --}}
    <div class="fixed inset-x-0 top-[12vh] mx-auto w-full max-w-xl px-4">
        <div
            x-show="show"
            x-transition
            @click.outside="close()"
            class="overflow-hidden rounded-xl border border-line bg-surface-overlay shadow-lg"
        >
            <div class="flex items-center gap-3 border-b border-line px-4">
                <x-ui.icon name="search" class="h-5 w-5 text-content-muted" />
                <input
                    x-ref="input"
                    x-model="query"
                    @input="onInput()"
                    @keydown.down.prevent="move(1)"
                    @keydown.up.prevent="move(-1)"
                    @keydown.enter.prevent="activate()"
                    @keydown.escape="close()"
                    type="text"
                    placeholder="Search or jump to…"
                    class="h-12 w-full bg-transparent text-sm text-content placeholder:text-content-muted focus:outline-none"
                >
                <kbd class="rounded border border-line px-1.5 py-0.5 text-[11px] text-content-muted">Esc</kbd>
            </div>

            <div class="max-h-80 overflow-y-auto p-2">
                <template x-for="(item, index) in filtered" :key="index">
                    <button
                        type="button"
                        @click="activate(item)"
                        @mouseenter="selected = index"
                        class="flex w-full items-center gap-3 rounded-md px-3 py-2 text-left text-sm"
                        :class="selected === index ? 'bg-primary-soft text-primary' : 'text-content-secondary'"
                    >
                        <span class="truncate" x-text="item.label"></span>
                        <span class="ml-auto text-xs text-content-muted" x-text="item.group"></span>
                    </button>
                </template>

                <p x-show="filtered.length === 0" class="px-3 py-6 text-center text-sm text-content-muted">
                    No results.
                </p>
            </div>
        </div>
    </div>
</div>
