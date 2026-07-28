@props([
    'productId',
    'floating' => false,
])

@if (app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('wishlist') && settings('catalog.wishlist_enabled', true))

@php $wishlistService = app(\Themicly\Shopcrafty\Core\Module\AddonRegistry::class)->all()['wishlist']['service'] ?? null; $active = $wishlistService ? app($wishlistService)->has((int) $productId) : false; @endphp

<button
    type="button"
    x-data="{ on: @js($active), busy: false }"
    x-on:click.prevent.stop="
        if (busy) return; busy = true;
        fetch('{{ route('storefront.wishlist.toggle') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
            body: JSON.stringify({ product_id: {{ (int) $productId }} }),
        }).then(r => r.json()).then(d => {
            on = d.active;
            window.dispatchEvent(new CustomEvent('wishlist-changed', { detail: d }));
        }).finally(() => busy = false)
    "
    :aria-pressed="on"
    :aria-label="on ? @js(__('storefront.remove_from_wishlist')) : @js(__('storefront.save_to_wishlist'))"
    {{ $attributes->merge(['class' => $floating
        ? 'absolute right-3 top-3 z-10 grid h-9 w-9 place-items-center rounded-full shadow-sm backdrop-blur'
        : 'grid h-10 w-10 place-items-center rounded-full border']) }}
    style="{{ $floating ? 'background: color-mix(in srgb, var(--st-bg) 80%, transparent)' : 'border-color: var(--st-line); background: var(--st-bg)' }}"
>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5"
        :fill="on ? 'var(--st-accent)' : 'none'" :stroke="on ? 'var(--st-accent)' : 'currentColor'" stroke-width="1.6"
        style="color: var(--st-ink-soft)">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
    </svg>
</button>
@endif
