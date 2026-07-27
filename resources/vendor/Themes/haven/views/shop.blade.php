@extends('theme::layout')

@section('title', __('storefront.shop'))
@section('meta_description', __('storefront.browse_all_products'))

@section('content')
    {{-- Haven shop index — a linen masthead: caps breadcrumb, a lowercase serif
         title closed by a hairline rule. Eyebrow/title/intro are theme text
         settings (editable in the customizer), falling back to the storefront
         translation strings. The catalog.product-browser livewire component
         (its own filter rail + grid) is preserved exactly below. --}}
    <section style="background: var(--st-bg); padding-block: clamp(2.5rem, 6vw, 4rem) 0">
        <div class="st-container">
            <nav class="stt-haven-crumb st-reveal mb-6 flex items-center gap-2" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">{{ __('storefront.home') }}</a>
                <span aria-hidden="true">/</span>
                <span style="color: var(--st-ink)">{{ __('storefront.shop') }}</span>
            </nav>

            <div class="stt-haven-head st-reveal" style="margin-bottom: 0">
                <p class="stt-haven-eyebrow mb-3">{{ $theme['text_shop_eyebrow'] ?? __('storefront.haven_shop_eyebrow') }}</p>
                <h1 class="stt-haven-display stt-haven-title">{{ $theme['text_shop_title'] ?? __('storefront.haven_shop_title') }}</h1>
                <p class="mt-4 max-w-xl text-sm leading-relaxed sm:text-base" style="color: var(--st-ink-soft)">
                    {{ $theme['text_shop_intro'] ?? __('storefront.haven_shop_intro') }}
                </p>
            </div>

            <div class="stt-haven-divider st-reveal mt-8" aria-hidden="true"></div>
        </div>
    </section>

    {{-- Catalog grid + filters (livewire) — preserved exactly --}}
    <div class="st-container" style="padding-top: clamp(2.5rem, 5vw, 3.5rem); padding-bottom: clamp(4rem, 8vw, 6rem)">
        <livewire:catalog.product-browser context="shop" />
    </div>
@endsection
