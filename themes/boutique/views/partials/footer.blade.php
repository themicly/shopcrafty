@php
    $storeName = settings('general.store_name', config('app.name'));
    $storeEmail = settings('general.store_email');
    $storePhone = settings('general.store_phone');
    $whatsapp = settings('general.whatsapp');
    $facebook = settings('general.facebook');
    $instagram = settings('general.instagram');
    $tree = app(\Themicly\Shopcrafty\Modules\Catalog\Contracts\CategoryTree::class)->roots()->take(5);
    $footerMenu = \Themicly\Shopcrafty\Modules\CMS\Models\Menu::where('location', 'footer')->first()?->items()->get() ?? collect();

    // Footer builder options (TASK #31).
    $showPaymentIcons = (bool) ($theme['footer_show_payment_icons'] ?? true);
    $paymentMethods = collect(preg_split('/[,\\r\\n]+/', (string) ($theme['footer_payment_methods'] ?? '')))->map(fn ($method) => trim($method))->filter()->unique()->values();
    $showNewsletter = (bool) ($theme['footer_newsletter'] ?? false) && settings('marketing.newsletter_enabled', true);
@endphp

{{-- Boutique footer (boutique-v2 pattern): multi-column — about/contact + social,
     services links, shop links, newsletter — closed by payment badges + copyright. --}}
<footer class="mt-20" style="background: var(--st-surface); border-top: 1px solid var(--st-line)">
    <div class="st-container py-16">
        {{-- Columns centre on phones (single column) and left-align from sm up. --}}
        <div class="grid gap-10 text-center sm:grid-cols-2 sm:text-left lg:grid-cols-4">
            {{-- About / contact --}}
            <div>
                <p class="text-lg font-bold uppercase" style="letter-spacing: 0.12em; color: var(--st-ink)">{{ $storeName }}</p>
                <span class="stt-boutique-mark mx-auto mt-3 sm:mx-0" aria-hidden="true"></span>
                <p class="mt-5 text-sm leading-relaxed" style="color: var(--st-ink-soft)">{{ $theme['footer_text'] ?? 'Fashion-forward pieces for the confident woman.' }}</p>
                <ul class="mt-5 space-y-2 text-sm" style="color: var(--st-ink-soft)">
                    @if ($storeEmail)<li><a href="mailto:{{ $storeEmail }}" class="hover:underline underline-offset-4">{{ $storeEmail }}</a></li>@endif
                    @if ($storePhone)<li><a href="tel:{{ preg_replace('/[^0-9+]/', '', $storePhone) }}" class="hover:underline underline-offset-4">{{ $storePhone }}</a></li>@endif
                    @if ($whatsapp)<li><a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}" class="hover:underline underline-offset-4">{{ $theme['footer_whatsapp_label'] ?? 'WhatsApp us' }}</a></li>@endif
                </ul>
                @if ($facebook || $instagram || $whatsapp)
                    <div class="mt-5 flex items-center justify-center gap-2 sm:justify-start" aria-label="{{ __('storefront.social_media') }}">
                        @if ($facebook)
                            <a href="{{ $facebook }}" target="_blank" rel="noopener" aria-label="Facebook" class="grid h-10 w-10 place-items-center transition-colors"
                                style="border: 1px solid var(--st-line); border-radius: var(--st-radius); color: var(--st-ink)">
                                <svg viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4"><path d="M13.5 21v-7h2.4l.5-3h-2.9V9.1c0-.9.3-1.6 1.7-1.6h1.3V4.8c-.6-.1-1.5-.2-2.5-.2-2.4 0-4 1.5-4 4.1V11H7.5v3H10v7h3.5Z"/></svg>
                            </a>
                        @endif
                        @if ($instagram)
                            <a href="{{ $instagram }}" target="_blank" rel="noopener" aria-label="Instagram" class="grid h-10 w-10 place-items-center transition-colors"
                                style="border: 1px solid var(--st-line); border-radius: var(--st-radius); color: var(--st-ink)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4"><rect x="3.5" y="3.5" width="17" height="17" rx="4.5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1" fill="currentColor" stroke="none"/></svg>
                            </a>
                        @endif
                        @if ($whatsapp)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}" target="_blank" rel="noopener" aria-label="WhatsApp" class="grid h-10 w-10 place-items-center transition-colors"
                                style="border: 1px solid var(--st-line); border-radius: var(--st-radius); color: var(--st-ink)">
                                <svg viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.71.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c0-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Services --}}
            <div>
                <p class="stt-boutique-label">{{ $theme['footer_services_heading'] ?? 'Services' }}</p>
                <ul class="mt-5 space-y-2.5 text-sm">
                    @foreach ($footerMenu as $item)
                        <li><a href="{{ $item->url }}" class="hover:underline underline-offset-4" style="color: var(--st-ink-soft)">{{ $item->label }}</a></li>
                    @endforeach
                    <li><a href="{{ url('/track') }}" class="hover:underline underline-offset-4" style="color: var(--st-ink-soft)">{{ $theme['footer_track_label'] ?? 'Track order' }}</a></li>
                    <li><a href="{{ url('/pages/about') }}" class="hover:underline underline-offset-4" style="color: var(--st-ink-soft)">{{ $theme['footer_about_label'] ?? 'About us' }}</a></li>
                    @if (app(\Themicly\Shopcrafty\Core\Module\AddonRegistry::class)->installed('blog'))<li><a href="{{ url('/blog') }}" class="hover:underline underline-offset-4" style="color: var(--st-ink-soft)">{{ $theme['footer_blog_label'] ?? 'Blog' }}</a></li>@endif
                </ul>
            </div>

            {{-- Shop --}}
            <div>
                <p class="stt-boutique-label">{{ $theme['footer_shop_heading'] ?? 'Shop' }}</p>
                <ul class="mt-5 space-y-2.5 text-sm">
                    <li><a href="{{ url('/shop') }}" class="hover:underline underline-offset-4" style="color: var(--st-ink-soft)">{{ $theme['footer_all_products_label'] ?? 'All products' }}</a></li>
                    @foreach ($tree as $category)
                        <li><a href="{{ url('/category/' . $category->slug) }}" class="hover:underline underline-offset-4" style="color: var(--st-ink-soft)">{{ $category->name }}</a></li>
                    @endforeach
                    <li>@if (app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('wishlist') && settings('catalog.wishlist_enabled', true))
@if (app(\Themicly\Shopcrafty\Core\Module\AddonRegistry::class)->installed('wishlist'))<a href="{{ route('storefront.wishlist') }}" class="hover:underline underline-offset-4" style="color: var(--st-ink-soft)">{{ $theme['footer_wishlist_label'] ?? 'Wishlist' }}</a>@endif
@endif</li>
                </ul>
            </div>

            {{-- Newsletter --}}
            @if ($showNewsletter)
                <div class="sm:col-span-2 lg:col-span-1">
                    <p class="stt-boutique-label">{{ $theme['footer_newsletter_heading'] ?? 'Newsletter' }}</p>
                    <div class="mt-5">
                        <livewire:marketing.newsletter-form heading="" subheading="" />
                    </div>
                </div>
            @else
                <div>
                    <p class="stt-boutique-label">{{ $theme['footer_perks_heading'] ?? 'Why shop with us' }}</p>
                    <ul class="mt-5 space-y-2.5 text-sm" style="color: var(--st-ink-soft)">
                        <li>{{ $theme['footer_perk_1'] ?? 'Free shipping over $85' }}</li>
                        <li>{{ $theme['footer_perk_2'] ?? 'Easy 14-day returns' }}</li>
                        <li>{{ $theme['footer_perk_3'] ?? 'Secure checkout' }}</li>
                        <li>{{ $theme['footer_perk_4'] ?? 'Support that answers' }}</li>
                    </ul>
                </div>
            @endif
        </div>

        @if ($showPaymentIcons)
            {{-- Payment badges: squared bordered chips. --}}
            <div class="mt-14 flex flex-wrap items-center justify-center gap-2" aria-label="{{ __('storefront.accepted_payment_methods') }}">
                @foreach ($paymentMethods as $method)
                    <span class="px-2.5 py-1.5 text-[10px] font-bold uppercase" style="letter-spacing: 0.1em; background: var(--st-bg); border: 1px solid var(--st-line); border-radius: var(--st-radius); color: var(--st-ink-soft)">{{ $method }}</span>
                @endforeach
            </div>
        @endif

        <div class="{{ $showPaymentIcons ? 'mt-8' : 'mt-14' }} flex flex-col items-center justify-between gap-3 border-t pt-8 text-[11px] font-semibold uppercase sm:flex-row" style="letter-spacing: 0.1em; border-color: var(--st-line); color: var(--st-ink-soft)">
            <p>&copy; {{ date('Y') }} {{ $storeName }}</p>
            <a href="https://themicly.com/shopcrafty" target="_blank" rel="noopener noreferrer"
                class="transition hover:opacity-70" aria-label="Powered by Shopcrafty">Powered by Shopcrafty</a>
        </div>
    </div>
</footer>
