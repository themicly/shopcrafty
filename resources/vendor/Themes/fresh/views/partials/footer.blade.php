@php
    use Illuminate\Support\Facades\Route;
    $storeName = settings('general.store_name', config('app.name'));
    $whatsapp = settings('general.whatsapp');
    $tree = app(\Themicly\Shopcrafty\Modules\Catalog\Contracts\CategoryTree::class)->roots()->take(5);
    $footerMenu = \Themicly\Shopcrafty\Modules\CMS\Models\Menu::where('location', 'footer')->first()?->items()->whereNull('parent_id')->orderBy('position')->get() ?? collect();

    // Footer builder options (TASK #31).
    $showPaymentIcons = (bool) ($theme['footer_show_payment_icons'] ?? true);
    $paymentMethods = collect(preg_split('/[,\\r\\n]+/', (string) ($theme['footer_payment_methods'] ?? '')))->map(fn ($method) => trim($method))->filter()->unique()->values();
    $showNewsletter = (bool) ($theme['footer_newsletter'] ?? false) && settings('marketing.newsletter_enabled', true);

    // Bloom's signature leaf glyph — the same outline path is reused wherever the leaf mark appears.
    $freshLeaf = 'M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12';
    // USP band icon paths: truck, heart, recycle arrows.
    $freshTruck = 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12';
    $freshHeart = 'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z';
    $freshRecycle = 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99';
@endphp

{{-- Bloom footer: reassurance-first, warm and fully rounded (grocery). --}}
<footer class="mt-16">
    {{-- Green USP reassurance band --}}
    <div style="background: var(--st-primary)">
        <div class="st-container grid grid-cols-2 gap-6 py-9 md:grid-cols-4" style="color: var(--st-primary-ink)">
            @foreach ([
                [$freshLeaf, $theme['footer_usp_1'] ?? 'Farm fresh daily'],
                [$freshTruck, $theme['footer_usp_2'] ?? 'Same-day delivery'],
                [$freshHeart, $theme['footer_usp_3'] ?? '100% happiness guarantee'],
                [$freshRecycle, $theme['footer_usp_4'] ?? 'Eco-friendly packaging'],
            ] as [$iconPath, $label])
                <div class="stt-fresh-usp">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" style="width: 1.75rem; height: 1.75rem" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}" /></svg>
                    <p class="text-sm font-semibold">{{ $label }}</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Soft-green panel --}}
    <div style="background: var(--st-surface)">
        @if ($showNewsletter)
            <div class="border-b" style="border-color: var(--st-line)">
                <div class="st-container py-12">
                    <livewire:marketing.newsletter-form :heading="$theme['footer_newsletter_heading'] ?? 'Join our newsletter'" :subheading="$theme['footer_newsletter_subheading'] ?? 'Get the latest offers and new arrivals in your inbox.'" />
                </div>
            </div>
        @endif
        <div class="st-container py-14">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div class="lg:col-span-2">
                    <p class="stt-fresh-heading flex items-center gap-2 text-3xl"><svg fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor" class="shrink-0" style="width: 1.6rem; height: 1.6rem; color: var(--st-primary)" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $freshLeaf }}" /></svg>{{ $storeName }}</p>
                    <p class="mt-3 max-w-sm text-sm leading-relaxed" style="color: var(--st-ink-soft)">{{ $theme['footer_text'] ?? 'Farm-fresh, delivered to your door.' }}</p>
                    <a href="{{ url('/shop') }}" class="stt-fresh-btn mt-6">{{ $theme['footer_cta_label'] ?? 'Start shopping' }}</a>
                </div>
                <div>
                    <p class="stt-fresh-eyebrow">{{ $theme['footer_categories_heading'] ?? 'Categories' }}</p>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        <li><a href="{{ url('/shop') }}" class="hover:opacity-70" style="color: var(--st-ink)">{{ $theme['footer_link_all_products'] ?? 'All products' }}</a></li>
                        @foreach ($tree as $category)
                            <li><a href="{{ url('/category/' . $category->slug) }}" class="hover:opacity-70" style="color: var(--st-ink)">{{ $category->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <p class="stt-fresh-eyebrow">{{ $theme['footer_help_heading'] ?? 'Help' }}</p>
                    <ul class="mt-4 space-y-2.5 text-sm">
                        @foreach ($footerMenu as $item)
                            <li><a href="{{ $item->url }}" class="hover:opacity-70" style="color: var(--st-ink)">{{ $item->label }}</a></li>
                        @endforeach
                        <li><a href="{{ route('storefront.support') }}" class="hover:opacity-70" style="color: var(--st-ink)">{{ $theme['footer_link_support'] ?? 'Help & Support' }}</a></li>
                        <li><a href="{{ url('/track') }}" class="hover:opacity-70" style="color: var(--st-ink)">{{ $theme['footer_link_track'] ?? 'Track order' }}</a></li>
                        @if ($whatsapp)
                            <li><a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}" class="hover:opacity-70" style="color: var(--st-ink)">{{ $theme['footer_link_whatsapp'] ?? 'WhatsApp us' }}</a></li>
                        @endif
                        <li><a href="{{ url('/sitemap.xml') }}" class="hover:opacity-70" style="color: var(--st-ink)">{{ $theme['footer_link_sitemap'] ?? 'Sitemap' }}</a></li>
                    </ul>
                </div>
            </div>

            @if ($showPaymentIcons)
                <div class="mt-12 flex flex-wrap items-center gap-2" aria-label="{{ __('storefront.accepted_payment_methods') }}">
                    @foreach ($paymentMethods as $method)
                        <span class="rounded-full border px-3 py-1 text-xs font-semibold" style="border-color: var(--st-line); background: var(--st-bg); color: var(--st-ink-soft)">{{ $method }}</span>
                    @endforeach
                </div>
            @endif

            <hr class="stt-fresh-divider mt-12">
            <div class="mt-6 flex flex-col items-center justify-between gap-3 text-xs sm:flex-row" style="color: var(--st-ink-soft)">
                {{-- Year + store name stay dynamic; only the trailing phrase is editable copy. --}}
                <p>&copy; {{ date('Y') }} {{ $storeName }}. {{ $theme['footer_copyright'] ?? 'All rights reserved.' }}</p>
                <p>{{ $theme['footer_powered_by'] ?? 'Powered by Shopcrafty' }}</p>
            </div>
        </div>
    </div>
</footer>
