@extends('theme::layout')

@section('title', $category->seo_title ?: $category->name)
@section('meta_description', $category->seo_description ?: __('storefront.shop_category', ['name' => $category->name]))

@section('content')
    <div class="st-container py-10 sm:py-14">
        <nav class="st-reveal mb-4 text-sm" style="color: var(--st-ink-soft)">
            <a href="{{ url('/shop') }}" class="hover:opacity-70">{{ __('storefront.shop') }}</a>
            <span class="mx-1.5">/</span>
            <span style="color: var(--st-ink)">{{ $category->name }}</span>
        </nav>

        <div class="st-reveal mb-8">
            <h1 class="st-display flex items-center gap-2.5 text-3xl font-semibold sm:text-4xl" style="color: var(--st-ink)">
                @if ($category->icon)<span class="leading-none">{{ $category->icon }}</span>@endif
                <span>{{ $category->name }}</span>
            </h1>
            @if ($category->description)
                <p class="mt-2 max-w-xl text-sm" style="color: var(--st-ink-soft)">{{ $category->description }}</p>
            @endif
        </div>

        <livewire:catalog.product-browser context="category" :category-id="$category->id" :key="'browse-cat-'.$category->id" />
    </div>
@endsection
