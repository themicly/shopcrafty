@extends('theme::layout')

@section('title', __('storefront.help_support'))

@php
    $storeName = settings('general.store_name', config('app.name'));
    $email = settings('general.store_email');
    $phone = settings('general.store_phone');
    $whatsapp = settings('general.whatsapp');
    $whatsappNumber = $whatsapp ? preg_replace('/[^0-9]/', '', $whatsapp) : null;

    // FAQ: pull from a CMS "faq" page's blocks when available, else a sensible default.
    $faqPage = \Themicly\Shopcrafty\Modules\CMS\Models\Page::published()->where('slug', 'faq')->first();
    $faqs = [];
    if ($faqPage) {
        foreach ((array) $faqPage->blocks as $block) {
            if (! empty($block['question']) || ! empty($block['q'])) {
                $faqs[] = [
                    'q' => $block['question'] ?? $block['q'],
                    'a' => $block['answer'] ?? ($block['a'] ?? ''),
                ];
            }
        }
    }
    if (empty($faqs)) {
        $faqs = [
            ['q' => __('storefront.faq_delivery_q'), 'a' => __('storefront.faq_delivery_a')],
            ['q' => __('storefront.faq_returns_q'), 'a' => __('storefront.faq_returns_a')],
            ['q' => __('storefront.faq_track_q'), 'a' => __('storefront.faq_track_a')],
            ['q' => __('storefront.faq_payment_q'), 'a' => __('storefront.faq_payment_a')],
        ];
    }
@endphp

@section('content')
    <div class="st-container py-12 sm:py-16">
        <div class="mb-10 text-center">
            <h1 class="st-display text-3xl font-semibold sm:text-4xl" style="color: var(--st-ink)">{{ __('storefront.help_support') }}</h1>
            <p class="mt-2 text-sm" style="color: var(--st-ink-soft)">{{ __('storefront.support_subtitle') }}</p>
        </div>

        <div class="grid gap-10 lg:grid-cols-2">
            {{-- Contact details + FAQ --}}
            <div class="space-y-8">
                <div>
                    <h2 class="st-display mb-4 text-lg font-semibold" style="color: var(--st-ink)">{{ __('storefront.contact_store', ['store' => $storeName]) }}</h2>
                    <ul class="space-y-3 text-sm">
                        @if ($email)
                            <li style="color: var(--st-ink)"><span style="color: var(--st-ink-soft)">{{ __('storefront.email_label') }}</span>
                                <a href="mailto:{{ $email }}" class="font-medium hover:opacity-70">{{ $email }}</a>
                            </li>
                        @endif
                        @if ($phone)
                            <li style="color: var(--st-ink)"><span style="color: var(--st-ink-soft)">{{ __('storefront.phone_label') }}</span>
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" class="font-medium hover:opacity-70">{{ $phone }}</a>
                            </li>
                        @endif
                        @if ($whatsappNumber)
                            <li>
                                <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener"
                                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold"
                                    style="background: var(--st-surface); color: var(--st-ink); border-radius: var(--st-radius-sm)">
                                    {{ __('storefront.chat_on_whatsapp') }}
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>

                <div>
                    <h2 class="st-display mb-4 text-lg font-semibold" style="color: var(--st-ink)">{{ __('storefront.faq_heading') }}</h2>
                    <div class="space-y-2">
                        @foreach ($faqs as $faq)
                            <details class="border p-4" style="border-color: var(--st-line); border-radius: var(--st-radius-sm)">
                                <summary class="cursor-pointer text-sm font-medium" style="color: var(--st-ink)">{{ $faq['q'] }}</summary>
                                <p class="mt-2 text-sm leading-relaxed" style="color: var(--st-ink-soft)">{{ $faq['a'] }}</p>
                            </details>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Contact form --}}
            <div>
                <div class="border p-6" style="border-color: var(--st-line); border-radius: var(--st-radius)">
                    <h2 class="st-display mb-4 text-lg font-semibold" style="color: var(--st-ink)">{{ __('storefront.send_us_message') }}</h2>
                    <livewire:customers.support-form />
                </div>
            </div>
        </div>
    </div>
@endsection
