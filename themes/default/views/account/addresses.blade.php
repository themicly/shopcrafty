@extends('theme::layout')

@section('title', __('storefront.addresses'))

@section('content')
    <div class="st-container py-12">
        <div class="flex flex-col gap-8 lg:flex-row">
            @include('theme::partials.account-nav')
            <div class="flex-1">
                <h1 class="st-display mb-6 text-2xl font-semibold" style="color: var(--st-ink)">{{ __('storefront.addresses') }}</h1>
                <livewire:customers.address-book />
            </div>
        </div>
    </div>
@endsection
