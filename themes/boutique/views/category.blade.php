@extends('theme::layout')

@section('title', $category->seo_title ?: $category->name)
@section('meta_description', $category->seo_description ?: __('storefront.shop_category_meta', ['name' => $category->name]))

@section('content')
    {{-- Boutique (Noir/Atelier): editorial head over the live category product browser. --}}
    <div class="stt-boutique-section" style="padding-block: 4rem;">
        <div class="st-container stt-boutique-narrow">
            <nav class="st-reveal stt-boutique-crumb mb-10 sm:mb-14" aria-label="{{ __('storefront.breadcrumb') }}">
                <a href="{{ url('/shop') }}">{{ __('storefront.shop') }}</a>
                <span style="margin-inline: 0.6rem; color: var(--st-line)">/</span>
                <span style="color: var(--st-ink)">{{ $category->name }}</span>
            </nav>

            <div class="st-reveal stt-boutique-section-head">
                <div class="stt-boutique-headrow">
                    <div class="flex flex-col gap-4">
                        <p class="stt-boutique-eyebrow">{{ __('storefront.boutique_category_eyebrow') }}</p>
                        <h1 class="stt-boutique-title flex items-center gap-3">
                            @if ($category->icon)<span class="leading-none">{{ $category->icon }}</span>@endif
                            <span>{{ $category->name }}</span>
                        </h1>
                    </div>
                    <span class="stt-boutique-mark mb-2 hidden sm:block"></span>
                </div>
            </div>

            @if ($category->description)
                <p class="st-reveal stt-boutique-measure mt-8" style="font-family: var(--st-font-body); font-weight: 300; font-size: 15px; line-height: 1.7; color: var(--st-ink-soft)">
                    {{ $category->description }}
                </p>
            @endif
        </div>
    </div>

    <div class="st-container stt-boutique-narrow" style="padding-bottom: 6rem;">
        <livewire:catalog.product-browser context="category" :category-id="$category->id" :key="'browse-cat-'.$category->id" />
    </div>
@endsection
