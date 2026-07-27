@php
    $addons = app(\Themicly\Shopcrafty\Core\Module\AddonRegistry::class);
    $channelLabels = ['email' => 'Email', 'sms' => 'SMS'];

    $notificationItems = [
        ['route' => 'admin.notifications.index', 'label' => 'Events & templates', 'desc' => 'What sends, and the wording', 'keywords' => 'notification event template email sms message wording order confirmation'],
    ];

    foreach (app(\Themicly\Shopcrafty\Modules\Notifications\Services\ProviderRegistry::class)->channels() as $channel) {
        $notificationItems[] = [
            'route' => 'admin.notifications.gateways',
            'params' => ['channel' => $channel],
            'label' => ($channelLabels[$channel] ?? ucfirst($channel)).' gateway',
            'desc' => 'Provider & credentials',
            'keywords' => "notification gateway provider credentials {$channel} smtp twilio vonage api",
        ];
    }

    // Logs get their own group below — notifications keeps only configuration.

    // Grouped settings navigation — relevant screens live together.
    $nav = [
        ['label' => 'Store', 'items' => [
            ['route' => 'admin.settings.index', 'label' => 'General', 'desc' => 'Store name, contact, socials', 'keywords' => 'store name brand contact email phone social whatsapp facebook logo'],
            ['route' => 'admin.settings.appearance', 'label' => 'Appearance', 'desc' => 'Admin panel colors', 'keywords' => 'appearance color colour theme brand admin panel customize customise primary accent sidebar button'],
            ['route' => 'admin.settings.localization', 'label' => 'Localization', 'desc' => 'Currency, country & formats', 'keywords' => 'currency money symbol country region timezone date format language locale taka dollar'],
            ['route' => 'admin.settings.demo', 'label' => 'Demo content', 'desc' => 'One-click sample store', 'keywords' => 'demo sample data import fashion electronics grocery furniture seed content starter'],
        ]],
        ['label' => 'Commerce', 'items' => [
            ['route' => 'admin.settings.payments', 'label' => 'Payments', 'desc' => 'Methods, order & keys', 'keywords' => 'payment stripe bkash cash cod gateway checkout keys api method'],
            ['route' => 'admin.settings.tax', 'label' => 'Tax', 'desc' => 'VAT / GST rate & mode', 'keywords' => 'tax vat gst rate inclusive exclusive duty'],
            ['route' => 'admin.settings.shipping', 'label' => 'Shipping', 'desc' => 'Delivery zones & rates', 'keywords' => 'shipping delivery zone rate courier freight postage carrier'],
            ['route' => 'admin.settings.locations', 'label' => 'Locations', 'desc' => 'Address areas & zone mapping', 'keywords' => 'location address area district city division zone mapping region'],
        ]],
        ['label' => 'Marketing', 'items' => [
        ]],
        ['label' => 'Notifications', 'items' => $notificationItems],
        ['label' => 'Team & security', 'items' => [
            ['route' => 'admin.settings.staff', 'label' => 'Staff', 'desc' => 'Team access & roles', 'keywords' => 'staff team member user role permission access owner invite'],
        ]],
        ['label' => 'System', 'items' => [
            ['route' => 'admin.settings.maintenance', 'label' => 'Maintenance', 'desc' => 'Cache & maintenance mode', 'keywords' => 'maintenance cache clear optimize downtime offline mode'],
            ['route' => 'admin.settings.addons', 'label' => 'Add-ons', 'desc' => 'Installed Shopcrafty packages', 'keywords' => 'addons extensions packages plugins modules installed'],
        ]],
    ];

    $nav = collect($nav)->map(function (array $group) use ($addons) {
        $group['items'] = collect($group['items'])
            ->filter(fn (array $item) => ! ($item['addon'] ?? null) || $addons->installed($item['addon']))
            ->values()->all();
        return $group;
    })->filter(fn (array $group) => ! empty($group['items']))->values()->all();

    // Flat list for the client-side quick search — spans every group,
    // notifications included.
    $searchItems = collect($nav)->flatMap(fn ($group) => collect($group['items'])->map(fn ($item) => [
        'label' => $item['label'],
        'desc' => $item['desc'],
        'group' => $group['label'],
        'keywords' => $item['keywords'] ?? '',
        'url' => route($item['route'], $item['params'] ?? []),
    ]))->values();
@endphp

<x-admin.section-shell title="Settings" subtitle="Configure how your store runs." :nav="$nav">
    {{-- Quick search: lives at the top of the settings sidebar and filters
         across every group, notifications included. --}}
    <x-slot:asideTop>
        <div
            x-data="{
                q: '',
                items: {{ Illuminate\Support\Js::from($searchItems) }},
                get results() {
                    const term = this.q.trim().toLowerCase();
                    if (term === '') return [];
                    return this.items.filter((i) =>
                        (i.label + ' ' + i.desc + ' ' + i.group + ' ' + i.keywords).toLowerCase().includes(term)
                    );
                },
            }"
            class="relative"
        >
            <label for="settings-search" class="sr-only">Search settings</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-content-muted">
                    <x-ui.icon name="search" class="h-4 w-4" />
                </span>
                <input
                    id="settings-search"
                    type="search"
                    x-model="q"
                    placeholder="Search settings…"
                    autocomplete="off"
                    class="h-9 w-full rounded-lg border border-line bg-surface-raised pl-9 pr-3 text-sm text-content placeholder:text-content-muted focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary"
                >
            </div>

            <div
                x-show="q.trim() !== ''"
                x-cloak
                class="absolute z-20 mt-2 w-full overflow-hidden rounded-lg border border-line bg-surface-raised shadow-lg"
            >
                <template x-if="results.length === 0">
                    <p class="px-3 py-3 text-sm text-content-muted">No matching settings.</p>
                </template>
                <template x-for="item in results" :key="item.url">
                    <a :href="item.url" class="block px-3 py-2 transition-colors hover:bg-surface-sunken">
                        <span class="flex items-baseline gap-2">
                            <span class="text-sm font-medium text-content" x-text="item.label"></span>
                            <span class="text-[11px] uppercase tracking-wide text-content-muted" x-text="item.group"></span>
                        </span>
                        <span class="block text-xs text-content-muted" x-text="item.desc"></span>
                    </a>
                </template>
            </div>
        </div>
    </x-slot:asideTop>

    {{ $slot }}
</x-admin.section-shell>
