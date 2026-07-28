@extends('theme::layout')

@section('title', __('storefront.compare_products'))

@section('content')
    <div class="st-container py-12">
        <h1 class="st-display mb-8 text-3xl font-semibold sm:text-4xl" style="color: var(--st-ink)">{{ __('storefront.compare') }}</h1>
        @if (app(\Themicly\Shopcrafty\Core\Module\AddonRegistry::class)->installed('compare'))
            <livewire:compare.compare-page />
        @endif
    </div>
@endsection
