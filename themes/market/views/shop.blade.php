@extends('theme::layout')

@section('title', __('storefront.shop'))
@section('meta_description', __('storefront.browse_all_products'))

@section('content')
    {{-- Marketplace shop: a boxed WoodMart-mould page head — breadcrumb strip on a soft
         surface field, then a title band with a red eyebrow, bold heading and short red
         underline rule. The product-browser livewire component (its own filter rail +
         high-density grid) is preserved exactly and carries the scanning weight below. --}}

    {{-- Breadcrumb strip --}}
    <div style="background: var(--st-surface); border-bottom: 1px solid var(--st-line)">
        <div class="st-container py-3">
            <nav class="stt-market-crumbs" aria-label="Breadcrumb">
                <a href="{{ url('/') }}">{{ __('storefront.home') }}</a>
                <span class="sep" aria-hidden="true">/</span>
                <span style="color: var(--st-ink); font-weight: 600">{{ __('storefront.shop') }}</span>
            </nav>
        </div>
    </div>

    {{-- Title band --}}
    <div style="background: var(--st-bg); border-bottom: 1px solid var(--st-line)">
        <div class="stt-market-page-pad st-container py-8">
            <div class="st-reveal">
                <p class="stt-market-eyebrow">{{ __('storefront.market_shop_eyebrow') }}</p>
                <h1 class="stt-market-title">{{ __('storefront.shop') }}</h1>
                <span class="stt-market-rule"></span>
            </div>
        </div>
    </div>

    {{-- High-density catalog grid + filters (livewire) --}}
    <div class="stt-market-page-pad st-container py-8">
        <livewire:catalog.product-browser context="shop" />
    </div>
@endsection
