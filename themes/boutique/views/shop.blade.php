@extends('theme::layout')

@section('title', __('storefront.shop'))
@section('meta_description', __('storefront.shop_meta_description'))

@section('content')
    {{-- Boutique (Noir/Atelier): editorial head over the live product browser. --}}
    <div class="stt-boutique-section" style="padding-block: 4rem;">
        <div class="st-container stt-boutique-narrow">
            <nav class="st-reveal stt-boutique-crumb mb-10 sm:mb-14" aria-label="{{ __('storefront.breadcrumb') }}">
                <a href="{{ url('/') }}">{{ __('storefront.home') }}</a>
                <span style="margin-inline: 0.6rem; color: var(--st-line)">/</span>
                <span style="color: var(--st-ink)">{{ __('storefront.shop') }}</span>
            </nav>

            <div class="st-reveal stt-boutique-section-head">
                <div class="stt-boutique-headrow">
                    <div class="flex flex-col gap-4">
                        <p class="stt-boutique-eyebrow">{{ __('storefront.boutique_shop_eyebrow') }}</p>
                        <h1 class="stt-boutique-title">{{ __('storefront.shop') }}</h1>
                    </div>
                    <span class="stt-boutique-mark mb-2 hidden sm:block"></span>
                </div>
            </div>

            <p class="st-reveal stt-boutique-measure mt-8" style="font-family: var(--st-font-body); font-weight: 300; font-size: 15px; line-height: 1.7; color: var(--st-ink-soft)">
                {{ __('storefront.boutique_shop_intro') }}
            </p>
        </div>
    </div>

    <div class="st-container stt-boutique-narrow" style="padding-bottom: 6rem;">
        <livewire:catalog.product-browser context="shop" />
    </div>
@endsection
