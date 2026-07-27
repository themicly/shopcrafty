@props([
    'productId',
    'floating' => false,
])

@if (app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('compare') && settings('catalog.compare_enabled', true))

@php $active = app(\Themicly\Shopcrafty\Modules\Catalog\Services\CompareService::class)->has((int) $productId); @endphp

<button
    type="button"
    x-data="{ on: @js($active), busy: false }"
    x-on:compare-changed.window="if ($event.detail && $event.detail.id === {{ (int) $productId }}) on = $event.detail.active"
    x-on:click.prevent.stop="
        if (busy) return; busy = true;
        fetch('{{ route('storefront.compare.toggle') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
            body: JSON.stringify({ product_id: {{ (int) $productId }} }),
        }).then(r => r.json()).then(d => {
            if (d.full) { window.toast?.('You can compare up to ' + d.max + ' products', 'error'); return; }
            on = d.active;
            window.dispatchEvent(new CustomEvent('compare-changed', { detail: { ...d, id: {{ (int) $productId }} } }));
            window.toast?.(d.active ? @js(__('storefront.added_to_compare')) : @js(__('storefront.removed_from_compare')), 'success');
        }).finally(() => busy = false)
    "
    :aria-pressed="on"
    :aria-label="on ? @js(__('storefront.remove_from_comparison')) : @js(__('storefront.add_to_comparison'))"
    title="{{ __('storefront.compare') }}"
    {{ $attributes->merge(['class' => $floating
        ? 'absolute right-3 top-14 z-10 grid h-9 w-9 place-items-center rounded-full shadow-sm backdrop-blur'
        : 'grid h-10 w-10 place-items-center rounded-full border']) }}
    style="{{ $floating ? 'background: color-mix(in srgb, var(--st-bg) 80%, transparent)' : 'border-color: var(--st-line); background: var(--st-bg)' }}"
>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5"
        :fill="'none'" :stroke="on ? 'var(--st-accent)' : 'currentColor'" stroke-width="1.6"
        style="color: var(--st-ink-soft)">
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5 7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5" />
    </svg>
</button>
@endif
