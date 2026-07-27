@extends('theme::layout')

@section('title', __('account.my_downloads'))

@section('content')
    <div class="st-container py-12">
        <div class="flex flex-col gap-8 lg:flex-row">
            @include('theme::partials.account-nav')

            <div class="flex-1">
                <h1 class="st-display mb-6 text-2xl font-semibold" style="color: var(--st-ink)">{{ __('storefront.downloads') }}</h1>

                @if ($lines->isEmpty())
                    <div class="border p-8 text-center" style="border-color: var(--st-line); border-radius: var(--st-radius)">
                        <p class="text-sm" style="color: var(--st-ink-soft)">{{ __('account.no_downloads') }}</p>
                        <a href="{{ url('/shop') }}" class="mt-3 inline-block text-sm font-medium" style="color: var(--st-ink)">{{ __('account.browse_shop') }}</a>
                    </div>
                @else
                    @include('theme::partials.download-lines', ['lines' => $lines, 'showOrder' => true])
                @endif
            </div>
        </div>
    </div>
@endsection
