@extends('theme::layout')

@section('title', $category->seo_title ?: $category->name)
@section('meta_description', $category->seo_description ?: 'Shop ' . $category->name)

@section('content')
    {{-- Studio category — the sage band masthead: caps breadcrumb, then the
         centered UPPERCASE serif title closed by its underline rule. The shared
         product-browser carries the grid beneath on the white canvas. --}}
    <section class="stt-studio-band" style="padding-block: clamp(2.5rem, 6vw, 4rem)">
        <div class="st-container">
            <nav class="stt-studio-crumb st-reveal mb-6 flex items-center justify-center gap-2" aria-label="Breadcrumb" style="color: var(--st-ink)">
                <a href="{{ url('/shop') }}">{{ __('storefront.shop') }}</a>
                <span aria-hidden="true">/</span>
                <span>{{ $category->name }}</span>
            </nav>

            <div class="st-reveal flex flex-col items-center text-center">
                <h1 class="stt-studio-title stt-studio-title--rule">
                    @if ($category->icon)<span class="leading-none" style="font-size: .8em">{{ $category->icon }}</span>@endif
                    {{ $category->name }}
                </h1>
                @if ($category->description)
                    <p class="mt-5 max-w-xl text-sm leading-relaxed sm:text-base" style="color: color-mix(in srgb, var(--st-ink) 72%, var(--st-band))">{{ $category->description }}</p>
                @endif
            </div>
        </div>
    </section>

    <div class="st-container" style="padding-top: clamp(2.5rem, 5vw, 3.5rem); padding-bottom: clamp(4rem, 8vw, 6rem)">
        <livewire:catalog.product-browser context="category" :category-id="$category->id" :key="'browse-cat-'.$category->id" />
    </div>
@endsection
