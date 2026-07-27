@php
    // Resolve a child menu item's tile image: its own `image`, else the linked
    // category's `image_path` when the URL points at /category/{slug} (TASK #33).
    $childImage = function ($child) use ($catImages) {
        if (! empty($child->image)) {
            return $child->image;
        }
        if (preg_match('#/category/([^/?\#]+)#', (string) $child->url, $m)) {
            return $catImages[$m[1]] ?? null;
        }

        return null;
    };
@endphp

{{-- Keyboard access: hover opens the dropdowns visually; focus-within mirrors that for
     tab users so submenu items are reachable without a pointer. --}}
@once
<style>
    nav .group:focus-within > [data-submenu] { visibility: visible; opacity: 1; transform: translateY(0); }
</style>
@endonce

<nav class="{{ $navClass ?? 'hidden items-center gap-6 md:flex' }}">
    <a href="{{ url('/shop') }}" class="text-sm font-medium hover:opacity-70" style="color: var(--st-ink)">{{ __('storefront.shop') }}</a>
    @if ($headerMenu->isNotEmpty())
        @foreach ($headerMenu as $item)
            @if ($item->children->isNotEmpty())
                @php
                    $children = $item->children;
                    $childImages = $children->mapWithKeys(fn ($c) => [$c->id => $childImage($c)]);
                    // A mega panel earns its space when there are several children or any imagery.
                    $isMega = $children->count() >= 4 || $childImages->filter()->isNotEmpty();
                @endphp
                <div class="group relative">
                    <a href="{{ $item->url }}" class="flex items-center gap-1 text-sm font-medium hover:opacity-70" style="color: var(--st-ink)">
                        {{ $item->label }}
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                    </a>
                    @if ($isMega)
                        {{-- Multi-column mega panel with images --}}
                        <div data-submenu class="invisible absolute left-0 top-full z-40 translate-y-1 pt-3 opacity-0 transition-all group-hover:visible group-hover:translate-y-0 group-hover:opacity-100">
                            <div class="rounded-xl border p-4 shadow-lg" style="border-color: var(--st-line); background: var(--st-bg)">
                                <div class="grid w-[min(88vw,720px)] grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                                    @foreach ($children as $child)
                                        @php $img = $childImages[$child->id]; @endphp
                                        <a href="{{ $child->url }}" class="group/tile flex flex-col gap-2 rounded-lg p-2 hover:bg-black/5" style="color: var(--st-ink)">
                                            @if ($img)
                                                <span class="block aspect-[4/3] overflow-hidden" style="border-radius: var(--st-radius-sm); background: var(--st-surface)">
                                                    <img src="{{ $img }}" alt="{{ $child->label }}" loading="lazy" class="h-full w-full object-cover transition-transform duration-300 group-hover/tile:scale-105">
                                                </span>
                                            @endif
                                            <span class="text-sm font-medium">{{ $child->label }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Simple single-column dropdown --}}
                        <div data-submenu class="invisible absolute left-0 top-full z-40 min-w-52 translate-y-1 pt-3 opacity-0 transition-all group-hover:visible group-hover:translate-y-0 group-hover:opacity-100">
                            <div class="rounded-xl border p-2 shadow-lg" style="border-color: var(--st-line); background: var(--st-bg)">
                                @foreach ($children as $child)
                                    <a href="{{ $child->url }}" class="block rounded-lg px-3 py-2 text-sm hover:opacity-70" style="color: var(--st-ink)">{{ $child->label }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <a href="{{ $item->url }}" class="text-sm font-medium hover:opacity-70" style="color: var(--st-ink)">{{ $item->label }}</a>
            @endif
        @endforeach
    @else
        @foreach ($tree->take(5) as $category)
            @php
                $catChildren = $category->children;
                $isMega = $catChildren->isNotEmpty() && ($catChildren->count() >= 4 || $catChildren->contains(fn ($c) => ! empty($c->image_path)));
            @endphp
            <div class="group relative">
                <a href="{{ url('/category/' . $category->slug) }}" class="flex items-center gap-1 text-sm font-medium hover:opacity-70" style="color: var(--st-ink)">
                    {{ $category->name }}
                    @if ($catChildren->isNotEmpty())
                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                    @endif
                </a>
                @if ($isMega)
                    <div data-submenu class="invisible absolute left-0 top-full z-40 translate-y-1 pt-3 opacity-0 transition-all group-hover:visible group-hover:translate-y-0 group-hover:opacity-100">
                        <div class="rounded-xl border p-4 shadow-lg" style="border-color: var(--st-line); background: var(--st-bg)">
                            <div class="grid w-[min(88vw,720px)] grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-4">
                                @foreach ($catChildren as $child)
                                    <a href="{{ url('/category/' . $child->slug) }}" class="group/tile flex flex-col gap-2 rounded-lg p-2 hover:bg-black/5" style="color: var(--st-ink)">
                                        @if ($child->image_path)
                                            <span class="block aspect-[4/3] overflow-hidden" style="border-radius: var(--st-radius-sm); background: var(--st-surface)">
                                                <img src="{{ $child->image_path }}" alt="{{ $child->name }}" loading="lazy" class="h-full w-full object-cover transition-transform duration-300 group-hover/tile:scale-105">
                                            </span>
                                        @endif
                                        <span class="text-sm font-medium">{{ $child->name }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @elseif ($catChildren->isNotEmpty())
                    <div data-submenu class="invisible absolute left-0 top-full z-40 min-w-52 translate-y-1 pt-3 opacity-0 transition-all group-hover:visible group-hover:translate-y-0 group-hover:opacity-100">
                        <div class="rounded-xl border p-2 shadow-lg" style="border-color: var(--st-line); background: var(--st-bg)">
                            @foreach ($catChildren as $child)
                                <a href="{{ url('/category/' . $child->slug) }}" class="block rounded-lg px-3 py-2 text-sm hover:bg-black/5" style="color: var(--st-ink)">{{ $child->name }}</a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    @endif
</nav>
