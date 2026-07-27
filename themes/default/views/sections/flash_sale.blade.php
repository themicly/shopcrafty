@php
    $products = \Themicly\Shopcrafty\Modules\Catalog\Models\Product::active()
        ->with('media')
        ->whereNotNull('compare_at_price')
        ->whereColumn('compare_at_price', '>', 'price')
        ->when(! empty($s['scope_categories']), fn ($q) => $q->whereIn('category_id', $s['scope_categories']))
        ->latest()
        ->limit((int) ($s['limit'] ?? 4))
        ->get();

    // Real countdown only when an end date is configured; otherwise no timer at all
    // (never ship frozen fake digits — UI-02).
    $endsAt = ! empty($s['ends_at']) ? \Illuminate\Support\Carbon::parse($s['ends_at']) : null;
    $showTimer = $endsAt && $endsAt->isFuture();
@endphp

@if ($products->isNotEmpty())
    <section class="py-16 sm:py-24" style="background: color-mix(in srgb, var(--st-accent) 8%, var(--st-bg))">
        <div class="st-container">
            <div class="st-reveal mb-10 flex flex-col gap-6 sm:mb-14 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <span class="mb-4 inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold uppercase tracking-[0.16em]"
                        style="background: var(--st-accent); color: #fff">
                        <span class="h-1.5 w-1.5 rounded-full" style="background: #fff"></span>
                        {{ __('storefront.limited_time') }}
                    </span>
                    <h2 class="st-display text-3xl font-semibold sm:text-4xl" style="color: var(--st-ink)">
                        {{ $s['heading'] ?? 'Flash Sale' }}
                    </h2>
                    <span aria-hidden="true" style="display: block; width: 3rem; height: 3px; margin-top: 0.875rem; border-radius: 999px; background: linear-gradient(90deg, var(--st-primary), var(--st-accent))"></span>
                </div>

                @if ($showTimer)
                    <div class="flex items-center gap-2 sm:gap-3"
                        x-data="{
                            target: new Date('{{ $endsAt->toIso8601String() }}').getTime(),
                            d: '00', h: '00', m: '00', s: '00',
                            tick() {
                                const sec = Math.max(0, Math.floor((this.target - Date.now()) / 1000));
                                this.d = String(Math.floor(sec / 86400)).padStart(2, '0');
                                this.h = String(Math.floor((sec % 86400) / 3600)).padStart(2, '0');
                                this.m = String(Math.floor((sec % 3600) / 60)).padStart(2, '0');
                                this.s = String(sec % 60).padStart(2, '0');
                            },
                            init() { this.tick(); this.timer = setInterval(() => this.tick(), 1000); },
                            destroy() { clearInterval(this.timer); },
                        }">
                        @foreach (['d' => __('storefront.days'), 'h' => __('storefront.hrs'), 'm' => __('storefront.min'), 's' => __('storefront.sec')] as $key => $label)
                            <div class="flex min-w-[3.5rem] flex-col items-center px-3 py-2"
                                style="background: var(--st-surface); border: 1px solid var(--st-line); border-radius: var(--st-radius)">
                                <span class="st-display text-xl font-semibold leading-none sm:text-2xl" style="color: var(--st-ink)" x-text="{{ $key }}">00</span>
                                <span class="mt-1 text-[10px] font-medium uppercase tracking-[0.12em]" style="color: var(--st-ink-soft)">{{ $label }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-4">
                @foreach ($products as $product)
                    <x-st.product-card :product="$product" />
                @endforeach
            </div>
        </div>
    </section>
@endif
