@extends('theme::layout')

@section('title', __('storefront.shop'))
@section('meta_description', __('storefront.shop_meta_description'))

@section('content')
    {{-- Bloom: the whole market stall — a warm cream band opens with the signature
         leaf eyebrow + Fraunces heading, then the live product browser (filters,
         grid & pagination) framed as a soft-green crate below. --}}
    <section class="stt-fresh-section" style="background: var(--st-bg)">
        <div class="st-container">
            <div class="st-reveal flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="stt-fresh-eyebrow">{{ __('storefront.fresh_from_the_market') }}</p>
                    <h1 class="stt-fresh-heading stt-fresh-title-lg mt-2 text-3xl sm:text-4xl">Shop <em>everything</em></h1>
                </div>
                @php
                    // Bloom's signature leaf glyph — the same outline path is reused wherever the leaf mark appears.
                    $freshLeaf = 'M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12';
                @endphp
                <span class="stt-fresh-badge--soft stt-fresh-badge" style="gap: 0.35rem"><svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 0.9rem; height: 0.9rem; flex-shrink: 0" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $freshLeaf }}" /></svg>{{ __('storefront.hand_picked_daily') }}</span>
            </div>

            <hr class="stt-fresh-divider mt-8">

            <div class="st-reveal mt-8">
                <livewire:catalog.product-browser context="shop" />
            </div>
        </div>
    </section>
@endsection
