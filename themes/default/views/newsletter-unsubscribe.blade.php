@extends('theme::layout')

@section('title', __('storefront.unsubscribe'))

@section('content')
    <div class="st-container py-24 text-center">
        @if ($ok)
            <h1 class="st-display text-3xl font-semibold" style="color: var(--st-ink)">{{ __('storefront.unsubscribed_heading') }}</h1>
            <p class="mt-3 text-sm" style="color: var(--st-ink-soft)">{{ __('storefront.unsubscribed_message') }}</p>
        @else
            <h1 class="st-display text-3xl font-semibold" style="color: var(--st-ink)">{{ __('storefront.link_not_recognised') }}</h1>
            <p class="mt-3 text-sm" style="color: var(--st-ink-soft)">{{ __('storefront.unsubscribe_invalid') }}</p>
        @endif
        <a href="{{ url('/') }}" class="mt-6 inline-flex text-sm font-medium" style="color: var(--st-accent)">{{ __('storefront.back_to_store') }}</a>
    </div>
@endsection
