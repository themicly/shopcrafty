@php
    // "Shop by gram" tiles sourced from recent product imagery (no extra data required).
    $limit = (int) ($s['limit'] ?? 6);
    $tiles = \Themicly\Shopcrafty\Modules\Catalog\Models\Product::active()
        ->with('media')
        ->when(! empty($s['scope_categories']), fn ($q) => $q->whereIn('category_id', $s['scope_categories']))
        ->latest()->limit($limit * 2)->get()
        ->filter(fn ($p) => $p->media->first()?->path)
        ->take($limit);
    $handle = '@' . \Illuminate\Support\Str::slug(settings('general.store_name', config('app.name')));
@endphp

@if ($tiles->isNotEmpty())
    {{-- Social gallery: shoppable grid linking to products (fashion demos). --}}
    <section class="py-16 sm:py-20" style="background: var(--st-bg)">
        <div class="st-container">
            <div class="st-reveal mb-8 text-center">
                <p class="mb-2 text-xs font-semibold uppercase tracking-[0.18em]" style="color: var(--st-accent)">{{ $handle }}</p>
                <h2 class="st-display text-2xl font-semibold sm:text-3xl" style="color: var(--st-ink)">{{ $s['heading'] ?? 'Shop by gram' }}</h2>
            </div>

            <div class="grid grid-cols-3 gap-2 sm:gap-3 lg:grid-cols-6">
                @foreach ($tiles as $product)
                    <a href="{{ url('/product/' . $product->slug) }}" class="group st-reveal relative block aspect-square overflow-hidden" style="border-radius: var(--st-radius)">
                        <img src="{{ $product->media->first()->path }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                        <span class="absolute inset-0 grid place-items-center opacity-0 transition-opacity duration-300 group-hover:opacity-100" style="background: color-mix(in srgb, var(--st-ink) 40%, transparent)">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="#fff" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" /></svg>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
