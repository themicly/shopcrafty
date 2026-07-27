@extends('theme::layout')

@section('title', __('storefront.shop'))
@section('meta_description', __('storefront.shop_meta'))

@section('content')
    <div class="st-container py-10 sm:py-14">
        <div class="st-reveal mb-8">
            <h1 class="st-display text-3xl font-semibold sm:text-4xl" style="color: var(--st-ink)">{{ __('storefront.shop') }}</h1>
            <span aria-hidden="true" style="display: block; width: 3rem; height: 3px; margin-top: 0.875rem; border-radius: 999px; background: linear-gradient(90deg, var(--st-primary), var(--st-accent))"></span>
        </div>

        <livewire:catalog.product-browser context="shop" />
    </div>
@endsection
