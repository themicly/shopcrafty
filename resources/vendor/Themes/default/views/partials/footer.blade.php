@php
    use Illuminate\Support\Facades\Route;

    $storeName = settings('general.store_name', config('app.name'));
    $whatsapp = settings('general.whatsapp');
    $tree = app(\Themicly\Shopcrafty\Modules\Catalog\Contracts\CategoryTree::class)->roots()->take(5);

    // Footer builder options (TASK #31).
    $showPaymentIcons = (bool) ($theme['footer_show_payment_icons'] ?? true);
    $paymentMethods = collect(preg_split('/[,\\r\\n]+/', (string) ($theme['footer_payment_methods'] ?? '')))->map(fn ($method) => trim($method))->filter()->unique()->values();
    $showNewsletter = (bool) ($theme['footer_newsletter'] ?? false) && settings('marketing.newsletter_enabled', true);
@endphp

<footer class="mt-20" style="background: var(--st-surface)">
    <hr class="stt-aurora-hairline" aria-hidden="true">
    @if ($showNewsletter)
        <div class="border-b" style="border-color: var(--st-line)">
            <div class="st-container py-12">
                <livewire:marketing.newsletter-form
                    :heading="$theme['footer_newsletter_heading'] ?? 'Join our newsletter'"
                    :subheading="$theme['footer_newsletter_subheading'] ?? 'Get the latest offers and new arrivals in your inbox.'" />
            </div>
        </div>
    @endif
    <div class="st-container py-14">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <p class="stt-aurora-wordmark text-2xl">{{ $storeName }}</p>
                <p class="mt-3 max-w-sm text-sm leading-relaxed" style="color: var(--st-ink-soft)">
                    {{ $theme['footer_text'] ?? 'Quality products, delivered.' }}
                </p>
                <div class="mt-6 flex flex-wrap gap-2">
                    @php
                        // Blanking a badge in the customizer removes its chip.
                        $badges = array_filter([
                            $theme['footer_badge_1'] ?? 'Secure checkout',
                            $theme['footer_badge_2'] ?? 'Fast delivery',
                            $theme['footer_badge_3'] ?? 'Easy returns',
                        ], fn ($b) => trim((string) $b) !== '');
                    @endphp
                    @foreach ($badges as $badge)
                        <span class="stt-aurora-chip">
                            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5" style="color: var(--st-primary)" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                            {{ $badge }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--st-primary)">{{ $theme['footer_shop_title'] ?? 'Shop' }}</p>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ url('/shop') }}" class="stt-aurora-link">{{ $theme['footer_all_products_label'] ?? 'All products' }}</a></li>
                    @foreach ($tree as $category)
                        <li><a href="{{ url('/category/' . $category->slug) }}" class="stt-aurora-link">{{ $category->name }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.14em]" style="color: var(--st-primary)">{{ $theme['footer_help_title'] ?? 'Help' }}</p>
                <ul class="mt-4 space-y-2.5 text-sm">
                    @php $footerMenu = \Themicly\Shopcrafty\Modules\CMS\Models\Menu::where('location', 'footer')->first()?->items()->whereNull('parent_id')->orderBy('position')->get() ?? collect(); @endphp
                    @foreach ($footerMenu as $item)
                        <li><a href="{{ $item->url }}" class="stt-aurora-link">{{ $item->label }}</a></li>
                    @endforeach
                    <li><a href="{{ route('storefront.support') }}" class="stt-aurora-link">{{ $theme['footer_support_label'] ?? 'Help & Support' }}</a></li>
                    @if ($whatsapp)
                        <li><a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}" class="stt-aurora-link">{{ $theme['footer_whatsapp_label'] ?? 'WhatsApp us' }}</a></li>
                    @endif
                    <li><a href="{{ url('/sitemap.xml') }}" class="stt-aurora-link">{{ $theme['footer_sitemap_label'] ?? 'Sitemap' }}</a></li>
                </ul>
            </div>
        </div>

        @if ($showPaymentIcons)
            <div class="mt-12 flex flex-wrap items-center gap-2" aria-label="{{ __('storefront.accepted_payment_methods') }}">
                @foreach ($paymentMethods as $method)
                    <span class="rounded-md border px-2.5 py-1 text-xs font-semibold" style="border-color: var(--st-line); background: var(--st-bg); color: var(--st-ink-soft)">{{ $method }}</span>
                @endforeach
            </div>
        @endif

        <div class="mt-12 flex flex-col items-center justify-between gap-3 border-t pt-6 text-xs sm:flex-row" style="border-color: var(--st-line); color: var(--st-ink-soft)">
            <p>&copy; {{ date('Y') }} {{ $storeName }}. {{ $theme['footer_copyright'] ?? 'All rights reserved.' }}</p>
            <p>{{ $theme['footer_powered_by'] ?? 'Powered by Shopcrafty' }}</p>
        </div>
    </div>
</footer>
