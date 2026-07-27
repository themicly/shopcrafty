@php
    use Illuminate\Support\Facades\Gate;
    use Illuminate\Support\Facades\Route;
    $addons = app(\Themicly\Shopcrafty\Core\Module\AddonRegistry::class);

    // A real "what needs my attention" feed, not a settings shortcut — each
    // item is a live count (same cheap-per-page-load pattern as the sidebar's
    // COD badge), gated by the same permission the item's own page requires.
    $alerts = [];

    if (Gate::allows('manage-orders')) {
        $codCount = \Themicly\Shopcrafty\Modules\Orders\Models\Order::where('cod_verification_status', 'unverified')->where('status', 'pending')->count();
        if ($codCount > 0) {
            $alerts[] = [
                'icon' => 'orders', 'variant' => 'warning',
                'text' => $codCount === 1 ? '1 order needs COD verification' : "{$codCount} orders need COD verification",
                'url' => route('admin.orders.index'),
            ];
        }
    }

    if (Gate::allows('manage-products')) {
        $lowStockCount = \Themicly\Shopcrafty\Modules\Catalog\Models\Product::where('track_inventory', true)->whereColumn('stock_qty', '<=', 'low_stock_threshold')->count();
        if ($lowStockCount > 0) {
            $alerts[] = [
                'icon' => 'warning', 'variant' => 'warning',
                'text' => $lowStockCount === 1 ? '1 product is low on stock' : "{$lowStockCount} products are low on stock",
                'url' => route('admin.catalog.inventory.index'),
            ];
        }

    }

@endphp

<div class="relative" x-data="{ open: false }" @keydown.escape="open = false">
    <button
        type="button"
        class="relative grid h-9 w-9 shrink-0 place-items-center rounded-md text-content-secondary hover:bg-surface-sunken"
        @click="open = !open"
        aria-label="Notifications ({{ count($alerts) }} unread)"
    >
        <x-ui.icon name="bell" />
        @if (count($alerts) > 0)
            <span class="absolute right-1.5 top-1.5 grid h-4 min-w-4 place-items-center rounded-full bg-danger px-1 text-[10px] font-bold leading-none text-white">
                {{ count($alerts) }}
            </span>
        @endif
    </button>

    <div
        x-show="open"
        x-transition
        @click.outside="open = false"
        x-cloak
        class="absolute right-0 z-30 mt-2 w-80 max-w-[calc(100vw-2rem)] overflow-hidden rounded-lg border border-line bg-surface-overlay shadow-lg"
        style="display: none;"
    >
        <div class="border-b border-line px-3 py-2.5">
            <p class="text-sm font-semibold text-content">Notifications</p>
        </div>

        @if (empty($alerts))
            <p class="px-3 py-6 text-center text-sm text-content-muted">You're all caught up.</p>
        @else
            <div class="max-h-80 divide-y divide-line overflow-y-auto">
                @foreach ($alerts as $alert)
                    <a href="{{ $alert['url'] }}" class="flex items-start gap-3 px-3 py-2.5 hover:bg-surface-sunken">
                        <span @class([
                            'mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-full',
                            'bg-warning-soft text-warning' => $alert['variant'] === 'warning',
                            'bg-info-soft text-info' => $alert['variant'] === 'info',
                        ])>
                            <x-ui.icon :name="$alert['icon']" class="h-4 w-4" />
                        </span>
                        <span class="text-sm text-content">{{ $alert['text'] }}</span>
                    </a>
                @endforeach
            </div>
        @endif

        @if (Route::has('admin.notifications.index') && Gate::allows('manage-config'))
            <a href="{{ route('admin.notifications.index') }}" class="block border-t border-line px-3 py-2 text-center text-xs font-medium text-content-muted hover:bg-surface-sunken hover:text-content">
                Notification settings
            </a>
        @endif
    </div>
</div>
