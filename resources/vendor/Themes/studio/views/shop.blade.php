@extends('theme::layout')

@section('title', __('storefront.shop'))
@section('meta_description', __('storefront.browse_all_products'))

@section('content')
    {{-- Studio shop index — the sage band masthead: caps breadcrumb over the
         centered UPPERCASE serif title with its underline rule. The
         catalog.product-browser livewire component (its own filter rail + grid)
         is preserved exactly below. --}}
    <section class="stt-studio-band" style="padding-block: clamp(2.5rem, 6vw, 4rem)">
        <div class="st-container">
            <nav class="stt-studio-crumb st-reveal mb-6 flex items-center justify-center gap-2" aria-label="Breadcrumb" style="color: var(--st-ink)">
                <a href="{{ url('/') }}">{{ __('storefront.home') }}</a>
                <span aria-hidden="true">/</span>
                <span>{{ __('storefront.shop') }}</span>
            </nav>

            <div class="st-reveal flex flex-col items-center text-center">
                <h1 class="stt-studio-title stt-studio-title--rule">{{ __('storefront.studio_shop_title') }}</h1>
                <p class="mt-5 max-w-xl text-sm leading-relaxed sm:text-base" style="color: color-mix(in srgb, var(--st-ink) 72%, var(--st-band))">
                    {{ __('storefront.studio_shop_intro') }}
                </p>
            </div>
        </div>
    </section>

    {{-- Catalog grid + filters (livewire) — preserved exactly --}}
    <div class="st-container" style="padding-top: clamp(2.5rem, 5vw, 3.5rem); padding-bottom: clamp(4rem, 8vw, 6rem)">
        <livewire:catalog.product-browser context="shop" />
    </div>
@endsection
