@extends('theme::layout')

@section('title', $q !== '' ? __('storefront.search_title', ['query' => $q]) : __('storefront.search'))

@section('content')
    <div class="st-container py-10 sm:py-14">
        <div class="st-reveal mb-8">
            <h1 class="st-display text-3xl font-semibold sm:text-4xl" style="color: var(--st-ink)">
                {{ $q !== '' ? __('storefront.showing_results_for', ['query' => $q]) : __('storefront.search') }}
            </h1>
        </div>

        <livewire:catalog.product-browser context="search" :q="$q" :key="'browse-search-'.$q" />
    </div>
@endsection
