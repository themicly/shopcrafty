@extends('theme::layout')

@section('title', __('checkout.track_your_order'))

@section('content')
    <div class="st-container py-12 sm:py-16">
        <div class="mb-8 text-center">
            <h1 class="st-display text-3xl font-semibold" style="color: var(--st-ink)">{{ __('checkout.track_your_order') }}</h1>
            <p class="mt-2 text-sm" style="color: var(--st-ink-soft)">{{ __('checkout.track_subtitle') }}</p>
        </div>
        <livewire:orders.order-tracker />
    </div>
@endsection
