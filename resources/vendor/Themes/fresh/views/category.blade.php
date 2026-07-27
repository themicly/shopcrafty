@extends('theme::layout')

@section('title', $category->seo_title ?: $category->name)
@section('meta_description', $category->seo_description ?: __('storefront.shop_category_meta', ['name' => $category->name]))

@section('content')
    @php
        // Bloom's signature leaf glyph — the same outline path is reused wherever the leaf mark appears.
        $freshLeaf = 'M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12';
    @endphp
    {{-- Bloom: a single market-stall band — warm cream surface, a rounded "aisle"
         breadcrumb, the category name set in Fraunces beside its emoji, then the live
         product browser (filters, grid & pagination) framed as a soft-green crate. --}}
    <section class="stt-fresh-section" style="background: var(--st-bg)">
        <div class="st-container">
            {{-- Rounded aisle breadcrumb --}}
            <nav class="st-reveal mb-6 flex items-center gap-1.5 text-sm" style="color: var(--st-ink-soft)">
                <a href="{{ url('/shop') }}"
                   class="stt-fresh-viewall"
                   style="background: color-mix(in srgb, var(--st-primary) 8%, var(--st-bg))">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4 shrink-0" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $freshLeaf }}" /></svg>
                    {{ __('storefront.shop') }}
                </a>
                <span aria-hidden="true" style="color: var(--st-line)">/</span>
                <span style="color: var(--st-ink); font-weight: 600">{{ $category->name }}</span>
            </nav>

            {{-- Heading row: leaf eyebrow + Fraunces name, with a friendly "farm fresh" pill --}}
            <div class="st-reveal flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="stt-fresh-eyebrow">{{ __('storefront.shop_the_aisle') }}</p>
                    <h1 class="stt-fresh-heading stt-fresh-title-lg mt-2 flex items-center gap-3 text-3xl sm:text-4xl">
                        @if ($category->icon)<span class="leading-none" style="font-size: 0.9em">{{ $category->icon }}</span>@endif
                        <span>{{ $category->name }}</span>
                    </h1>
                    @if ($category->description)
                        <p class="mt-3 max-w-xl leading-relaxed" style="color: var(--st-ink-soft)">{{ $category->description }}</p>
                    @endif
                </div>
                <span class="stt-fresh-badge stt-fresh-badge--soft" style="gap: 0.35rem"><svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 0.9rem; height: 0.9rem; flex-shrink: 0" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $freshLeaf }}" /></svg>{{ __('storefront.farm_fresh_picked_daily') }}</span>
            </div>

            {{-- Organic dashed leaf-rule --}}
            <hr class="stt-fresh-divider mt-8">

            {{-- Live product browser (filters, basket grid & pagination) --}}
            <div class="st-reveal mt-8">
                <livewire:catalog.product-browser context="category" :category-id="$category->id" :key="'browse-cat-'.$category->id" />
            </div>
        </div>
    </section>
@endsection
