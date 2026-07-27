@extends('theme::layout')

@section('title', $category->seo_title ?: $category->name)
@section('meta_description', $category->seo_description ?: 'Shop ' . $category->name)

@section('content')
    {{-- Haven category — the room's own masthead: caps breadcrumb, ghost numeral
         behind the lowercase serif room name, hairline rule beneath. The shared
         product-browser carries the grid on the linen canvas below. --}}
    <section style="background: var(--st-bg); padding-block: clamp(2.5rem, 6vw, 4rem) 0">
        <div class="st-container">
            <nav class="stt-haven-crumb st-reveal mb-6 flex items-center gap-2" aria-label="Breadcrumb">
                <a href="{{ url('/shop') }}">{{ __('storefront.shop') }}</a>
                <span aria-hidden="true">/</span>
                <span style="color: var(--st-ink)">{{ $category->name }}</span>
            </nav>

            <div class="stt-haven-head st-reveal" style="margin-bottom: 0">
                <span class="stt-haven-numeral" aria-hidden="true">{{ mb_strtolower(mb_substr($category->name, 0, 2)) }}</span>
                <p class="stt-haven-eyebrow mb-3">{{ __('storefront.haven_category_eyebrow') }}</p>
                <h1 class="stt-haven-display stt-haven-title">
                    @if ($category->icon)<span class="leading-none" style="font-size: .8em">{{ $category->icon }}</span>@endif
                    {{ $category->name }}
                </h1>
                @if ($category->description)
                    <p class="mt-4 max-w-xl text-sm leading-relaxed sm:text-base" style="color: var(--st-ink-soft)">{{ $category->description }}</p>
                @endif
            </div>

            <div class="stt-haven-divider st-reveal mt-8" aria-hidden="true"></div>
        </div>
    </section>

    <div class="st-container" style="padding-top: clamp(2.5rem, 5vw, 3.5rem); padding-bottom: clamp(4rem, 8vw, 6rem)">
        <livewire:catalog.product-browser context="category" :category-id="$category->id" :key="'browse-cat-'.$category->id" />
    </div>
@endsection
