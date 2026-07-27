@extends('theme::layout')

@section('title', $category->seo_title ?: $category->name)
@section('meta_description', $category->seo_description ?: 'Shop ' . $category->name)

@section('content')
    <div class="stt-market-section stt-market-section--bg" style="padding-block: 2rem;">
        <div class="st-container">
            <nav class="stt-market-crumbs st-reveal mb-4">
                <a href="{{ url('/shop') }}">{{ __('storefront.shop') }}</a>
                <span class="sep">/</span>
                <span style="color: var(--st-ink)">{{ $category->name }}</span>
            </nav>

            <div class="stt-market-box st-reveal" style="padding: 1.25rem 1.5rem;">
                <p class="stt-market-eyebrow" style="margin-bottom: 0.375rem;">{{ __('storefront.market_category_eyebrow') }}</p>
                <h1 class="st-display flex items-center gap-2.5" style="font-size: 1.75rem; font-weight: 700; letter-spacing: -0.01em; line-height: 1.15; color: var(--st-ink)">
                    @if ($category->icon)<span class="leading-none">{{ $category->icon }}</span>@endif
                    <span>{{ $category->name }}</span>
                </h1>
                <span class="stt-market-rule"></span>
                @if ($category->description)
                    <p class="mt-3 max-w-xl" style="font-size: 0.875rem; color: var(--st-ink-soft)">{{ $category->description }}</p>
                @endif
            </div>
        </div>
    </div>

    <div class="stt-market-section stt-market-section--surface" style="padding-block: 2.5rem;">
        <div class="st-container">
            <livewire:catalog.product-browser context="category" :category-id="$category->id" :key="'browse-cat-'.$category->id" />
        </div>
    </div>
@endsection
