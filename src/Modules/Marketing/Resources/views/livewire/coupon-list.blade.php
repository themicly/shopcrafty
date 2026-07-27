<div>
    <div class="mb-4 max-w-sm">
        <x-ui.input wire:model.live.debounce.400ms="search" placeholder="Search code or name…" />
        <p class="text-sm text-content-muted mt-2">Showing {{ $coupons->total() }} coupon(s)</p>
    </div>

    @if ($coupons->isEmpty())
        <div class="rounded-lg border border-line bg-surface-raised">
            <x-ui.empty-state icon="marketing" title="No coupons yet" description="Create a coupon to offer discounts at checkout.">
                <x-slot:action>
                    <x-ui.button :href="route('admin.marketing.coupons.create')" size="sm">Add coupon</x-ui.button>
                </x-slot:action>
            </x-ui.empty-state>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($coupons as $coupon)
                @php
                    $status = $coupon->statusLabel();
                    $statusVariant = match ($status) {
                        'active' => 'success',
                        'scheduled' => 'info',
                        default => 'neutral',
                    };
                    // Big value shown on the ticket stub.
                    [$stubValue, $stubSub] = match ($coupon->type) {
                        'percentage' => [$coupon->value . '%', 'off'],
                        'fixed' => [format_money($coupon->value), 'off'],
                        'free_shipping' => ['FREE', 'shipping'],
                        'bogo' => ['BOGO', 'buy ' . $coupon->buy_qty . ' get ' . $coupon->get_qty],
                        default => [strtoupper($coupon->type), ''],
                    };
                    $isMuted = $status !== 'active';
                @endphp

                <div wire:key="coupon-{{ $coupon->id }}"
                    @class([
                        'relative flex overflow-hidden rounded-xl border bg-surface-raised shadow-sm transition-shadow hover:shadow-md',
                        'border-line' => ! $isMuted,
                        'border-line opacity-70' => $isMuted,
                    ])>
                    {{-- Ticket stub --}}
                    <div class="flex w-24 shrink-0 flex-col items-center justify-center gap-0.5 px-2 py-5 text-center"
                        style="background: color-mix(in srgb, var(--bz-primary) 10%, var(--bz-surface-raised))">
                        <span class="text-2xl font-extrabold leading-none text-primary">{{ $stubValue }}</span>
                        @if ($stubSub)
                            <span class="text-[10px] font-semibold uppercase tracking-wide text-primary/70">{{ $stubSub }}</span>
                        @endif
                    </div>

                    {{-- Perforation with notches cut out of the card --}}
                    <div class="relative border-l-2 border-dashed border-line">
                        <span class="absolute -left-[9px] -top-2 h-4 w-4 rounded-full bg-surface"></span>
                        <span class="absolute -left-[9px] -bottom-2 h-4 w-4 rounded-full bg-surface"></span>
                    </div>

                    {{-- Body --}}
                    <div class="flex min-w-0 flex-1 flex-col gap-2 p-4">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <a href="{{ route('admin.marketing.coupons.edit', $coupon) }}" class="font-mono text-sm font-bold text-content hover:text-primary hover:underline focus-visible:underline focus-visible:outline-none">{{ $coupon->code }}</a>
                                @if ($coupon->name)
                                    <p class="truncate text-xs text-content-muted">{{ $coupon->name }}</p>
                                @endif
                            </div>
                            <x-ui.badge :variant="$statusVariant">{{ $status }}</x-ui.badge>
                        </div>

                        <div class="mt-auto flex items-center justify-between gap-2 text-xs text-content-muted">
                            <span>
                                {{ $coupon->used_count }} / {{ $coupon->usage_limit ?? '∞' }} used
                                @if ($coupon->ends_at)
                                    · until {{ $coupon->ends_at->format('M j') }}
                                @endif
                            </span>
                            {{-- Copy code to clipboard --}}
                            <button type="button"
                                x-data="{ copied: false }"
                                @click="navigator.clipboard.writeText(@js($coupon->code)); copied = true; setTimeout(() => copied = false, 1500)"
                                class="inline-flex items-center gap-1 rounded-md px-1.5 py-1 font-medium text-content-secondary transition-colors hover:bg-surface-sunken hover:text-content"
                                :class="copied && 'text-success'">
                                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="h-3.5 w-3.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" /></svg>
                                <span x-text="copied ? 'Copied' : 'Copy'">Copy</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-5">{{ $coupons->links() }}</div>
    @endif
</div>
