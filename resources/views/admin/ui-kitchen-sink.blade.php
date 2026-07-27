<x-layouts.admin title="UI Kit">
    <div class="space-y-8">
        <div>
            <h2 class="text-xl font-semibold text-content">Core/UI kit</h2>
            <p class="mt-1 text-sm text-content-muted">Every shared component, in the current theme. Toggle dark mode in the topbar.</p>
        </div>

        {{-- Buttons --}}
        <x-ui.card title="Buttons">
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.button variant="primary">Primary</x-ui.button>
                <x-ui.button variant="secondary">Secondary</x-ui.button>
                <x-ui.button variant="ghost">Ghost</x-ui.button>
                <x-ui.button variant="danger">Danger</x-ui.button>
                <x-ui.button variant="primary" size="sm">Small</x-ui.button>
                <x-ui.button variant="secondary" icon><x-ui.icon name="plus" class="h-4 w-4" /></x-ui.button>
            </div>
        </x-ui.card>

        {{-- Badges --}}
        <x-ui.card title="Badges / status chips">
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.badge>Neutral</x-ui.badge>
                <x-ui.badge variant="primary">Primary</x-ui.badge>
                <x-ui.badge variant="success">Paid</x-ui.badge>
                <x-ui.badge variant="warning">Pending</x-ui.badge>
                <x-ui.badge variant="danger">Failed</x-ui.badge>
                <x-ui.badge variant="info">Info</x-ui.badge>
            </div>
        </x-ui.card>

        {{-- Form controls --}}
        <x-ui.card title="Form controls">
            <div class="grid gap-5 sm:grid-cols-2">
                <x-ui.input name="ks_text" label="Text input" placeholder="Type here" hint="A helper hint." />
                <x-ui.input name="ks_err" label="With error" value="oops" error="This field is required." />
                <x-ui.select name="ks_select" label="Select">
                    <option>Draft</option><option>Active</option><option>Archived</option>
                </x-ui.select>
                <x-ui.money-input name="ks_price" label="Money input" symbol="$" placeholder="0.00" />
                <x-ui.textarea name="ks_area" label="Textarea" placeholder="Longer text…" />
                <x-ui.combobox name="ks_combo" label="Combobox (searchable)" :options="[
                    ['value' => '1', 'label' => 'Dhaka'],
                    ['value' => '2', 'label' => 'Chittagong'],
                    ['value' => '3', 'label' => 'Khulna'],
                    ['value' => '4', 'label' => 'Rajshahi'],
                ]" />
                <div class="space-y-3">
                    <p class="text-sm font-medium text-content-secondary">Choices</p>
                    <x-ui.checkbox name="ks_c1" label="Track inventory" checked />
                    <x-ui.radio name="ks_r" label="Option A" checked />
                    <x-ui.radio name="ks_r" label="Option B" />
                </div>
                <div class="space-y-3">
                    <p class="text-sm font-medium text-content-secondary">Toggle &amp; stepper</p>
                    <x-ui.toggle name="ks_t" label="Enable feature" checked />
                    <x-ui.quantity-stepper name="ks_qty" :value="1" :min="0" :max="10" />
                </div>
            </div>
        </x-ui.card>

        {{-- Stat cards + chart --}}
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <x-ui.stat-card label="Revenue" value="$12.4k" :delta="8" hint="30 days" />
            <x-ui.stat-card label="Orders" value="316" :delta="-3" hint="30 days" />
            <x-ui.stat-card label="Customers" value="1,204" :delta="12" hint="30 days" />
            <x-ui.stat-card label="Trend" value="—">
                <x-slot:sparkline>
                    <x-ui.chart :points="[4, 8, 6, 10, 7, 12, 14]" type="line" class="h-8 w-24" />
                </x-slot:sparkline>
            </x-ui.stat-card>
        </div>

        {{-- Table --}}
        <x-ui.card title="Data table">
            <x-ui.table flush>
                <thead>
                    <tr>
                        <th>Product</th><th>Status</th><th>Stock</th><th class="text-right">Price</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-medium">Classic Tee</td>
                        <td><x-ui.badge variant="success">Active</x-ui.badge></td>
                        <td>124</td>
                        <td class="text-right">$19.00</td>
                    </tr>
                    <tr>
                        <td class="font-medium">Denim Jacket</td>
                        <td><x-ui.badge variant="warning">Low stock</x-ui.badge></td>
                        <td>3</td>
                        <td class="text-right">$89.00</td>
                    </tr>
                    <tr>
                        <td class="font-medium">Canvas Bag</td>
                        <td><x-ui.badge>Draft</x-ui.badge></td>
                        <td>0</td>
                        <td class="text-right">$29.00</td>
                    </tr>
                </tbody>
            </x-ui.table>
        </x-ui.card>

        {{-- Tabs --}}
        <x-ui.card title="Tabs">
            <x-ui.tabs :tabs="[['key' => 'general', 'label' => 'General'], ['key' => 'pricing', 'label' => 'Pricing'], ['key' => 'seo', 'label' => 'SEO']]">
                <div x-show="tab === 'general'" class="text-sm text-content-secondary">General panel content.</div>
                <div x-show="tab === 'pricing'" class="text-sm text-content-secondary" x-cloak>Pricing panel content.</div>
                <div x-show="tab === 'seo'" class="text-sm text-content-secondary" x-cloak>SEO panel content.</div>
            </x-ui.tabs>
        </x-ui.card>

        {{-- Overlays + toasts --}}
        <x-ui.card title="Overlays, feedback & misc">
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.button variant="secondary" x-on:click="$dispatch('open-modal', 'ks-modal')">Open modal</x-ui.button>
                <x-ui.button variant="secondary" x-on:click="$dispatch('open-drawer', 'ks-drawer')">Open drawer</x-ui.button>
                <x-ui.button variant="danger" x-on:click="$dispatch('open-modal', 'ks-confirm')">Confirm dialog</x-ui.button>
                <x-ui.button variant="secondary" x-on:click="window.toast('Saved successfully', 'success')">Success toast</x-ui.button>
                <x-ui.button variant="secondary" x-on:click="window.toast('Something went wrong', 'danger')">Error toast</x-ui.button>

                <x-ui.dropdown>
                    <x-slot:trigger>
                        <x-ui.button variant="secondary">Dropdown</x-ui.button>
                    </x-slot:trigger>
                    <x-ui.dropdown-item>Edit</x-ui.dropdown-item>
                    <x-ui.dropdown-item>Duplicate</x-ui.dropdown-item>
                    <x-ui.dropdown-item>Archive</x-ui.dropdown-item>
                </x-ui.dropdown>

                <x-ui.tooltip text="Helpful hint">
                    <x-ui.button variant="ghost">Hover me</x-ui.button>
                </x-ui.tooltip>

                <x-ui.avatar name="Store Owner" />
            </div>

            <div class="mt-5 space-y-2">
                <p class="text-sm font-medium text-content-secondary">Skeletons</p>
                <x-ui.skeleton class="h-4 w-48" />
                <x-ui.skeleton class="h-4 w-64" />
                <x-ui.skeleton class="h-4 w-32" />
            </div>

            <div class="mt-5 rounded-lg border border-dashed border-line">
                <x-ui.empty-state icon="products" title="No products yet" description="Add your first product to get started." tone="neutral">
                    <x-slot:action>
                        <x-ui.button variant="primary" size="sm">Add product</x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            </div>
        </x-ui.card>
    </div>

    {{-- Overlay instances --}}
    <x-ui.modal name="ks-modal" title="Example modal">
        <p class="text-sm text-content-secondary">This is a modal dialog built from tokens. Press Esc, click the backdrop, or the × to close.</p>
        <x-slot:footer>
            <x-ui.button variant="ghost" x-on:click="$dispatch('close-modal', 'ks-modal')">Close</x-ui.button>
            <x-ui.button variant="primary" x-on:click="$dispatch('close-modal', 'ks-modal'); window.toast('Confirmed', 'success')">Save</x-ui.button>
        </x-slot:footer>
    </x-ui.modal>

    <x-ui.drawer name="ks-drawer" title="Example drawer">
        <p class="text-sm text-content-secondary">A right-side slide-over — used for cart, quick edits, and filters.</p>
    </x-ui.drawer>

    <x-ui.confirm-dialog name="ks-confirm" title="Delete product?" message="This action cannot be undone." confirm-label="Delete">
        <x-ui.button variant="danger" x-on:click="$dispatch('close-modal', 'ks-confirm'); window.toast('Deleted', 'danger')">Delete</x-ui.button>
    </x-ui.confirm-dialog>
</x-layouts.admin>
