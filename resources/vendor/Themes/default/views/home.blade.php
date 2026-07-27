@extends('theme::layout')

@section('content')
    @foreach ($sections as $section)
        @continue($section->section_key === 'newsletter' && ! settings('marketing.newsletter_enabled', true))
        @includeIf('theme::sections.' . $section->section_key, ['s' => $section->resolved_settings])
    @endforeach

    @if ($sections->isEmpty())
        <div class="mx-auto max-w-6xl px-4 py-24 text-center">
            <h1 class="text-2xl font-bold" style="color: var(--st-ink)">{{ settings('general.store_name', config('app.name')) }}</h1>
            <p class="mt-2" style="color: var(--st-ink-soft)">{{ __('storefront.storefront_ready') }}</p>
        </div>
    @endif
@endsection
