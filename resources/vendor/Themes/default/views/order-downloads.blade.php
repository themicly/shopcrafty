@extends('theme::layout')

@section('title', __('storefront.your_downloads'))

@section('content')
    <div class="st-container max-w-2xl py-12">
        <h1 class="st-display mb-2 text-2xl font-semibold" style="color: var(--st-ink)">{{ __('storefront.your_downloads') }}</h1>
        <p class="mb-8 text-sm" style="color: var(--st-ink-soft)">{{ __('storefront.downloads_ready', ['number' => $order->number]) }}</p>

        @include('theme::partials.download-lines', ['lines' => $lines])

        <div class="mt-8">
            <a href="{{ url('/shop') }}" class="text-sm font-medium" style="color: var(--st-ink)">← {{ __('storefront.continue_shopping') }}</a>
        </div>
    </div>
@endsection
