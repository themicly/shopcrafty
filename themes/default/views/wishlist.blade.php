@extends('theme::layout')

@section('title', __('storefront.my_wishlist'))

@section('content')
    <div class="st-container py-12">
        <h1 class="st-display mb-8 text-3xl font-semibold sm:text-4xl" style="color: var(--st-ink)">{{ __('storefront.wishlist') }}</h1>
        <livewire:customers.wishlist-page />
    </div>
@endsection
