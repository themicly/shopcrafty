@php
    use Illuminate\Support\Facades\Route;

    $storeName = settings('general.store_name', config('app.name'));
    $storeEmail = settings('general.store_email');
    $storePhone = settings('general.store_phone');
    $whatsapp = settings('general.whatsapp');
    $facebook = settings('general.facebook');
    $instagram = settings('general.instagram');
    $tree = app(\Themicly\Shopcrafty\Modules\Catalog\Contracts\CategoryTree::class)->roots()->take(5);
    $footerMenu = \Themicly\Shopcrafty\Modules\CMS\Models\Menu::where('location', 'footer')->first()?->items()->whereNull('parent_id')->orderBy('position')->get() ?? collect();

    // Footer builder options (TASK #31).
    $showPaymentIcons = (bool) ($theme['footer_show_payment_icons'] ?? true);
    $paymentMethods = collect(preg_split('/[,\\r\\n]+/', (string) ($theme['footer_payment_methods'] ?? '')))->map(fn ($method) => trim($method))->filter()->unique()->values();
    $showNewsletter = (bool) ($theme['footer_newsletter'] ?? false) && settings('marketing.newsletter_enabled', true);
@endphp

{{-- Studio footer: solid black band — serif caps column labels over quiet link
     lists, social squares, closed by a thin hairline copyright bar. --}}
<footer class="stt-studio-invert mt-20" style="background: var(--st-ink)">
    <div class="st-container py-16">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Need help --}}
            <div>
                <p class="stt-studio-footlabel">{{ $theme['footer_help_heading'] ?? 'Need help' }}</p>
                <ul class="mt-5 space-y-2.5">
                    @foreach ($footerMenu as $item)
                        <li><a href="{{ $item->url }}" class="stt-studio-footlink">{{ $item->label }}</a></li>
                    @endforeach
                    <li><a href="{{ route('storefront.support') }}" class="stt-studio-footlink">{{ $theme['footer_link_support'] ?? 'Help & support' }}</a></li>
                    <li><a href="{{ url('/track') }}" class="stt-studio-footlink">{{ $theme['footer_link_track'] ?? 'Track order' }}</a></li>
                    @if ($whatsapp)
                        <li><a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}" class="stt-studio-footlink">{{ $theme['footer_link_whatsapp'] ?? 'WhatsApp us' }}</a></li>
                    @endif
                </ul>
            </div>

            {{-- Company --}}
            <div>
                <p class="stt-studio-footlabel">{{ $theme['footer_company_heading'] ?? 'Company' }}</p>
                <ul class="mt-5 space-y-2.5">
                    <li><a href="{{ url('/pages/about') }}" class="stt-studio-footlink">{{ $theme['footer_link_about'] ?? 'About us' }}</a></li>
                    <li><a href="{{ url('/blog') }}" class="stt-studio-footlink">{{ $theme['footer_link_blog'] ?? 'Blog' }}</a></li>
                    <li>@if (app('Themicly\Shopcrafty\Core\Module\AddonRegistry')->installed('wishlist') && settings('catalog.wishlist_enabled', true))
<a href="{{ route('storefront.wishlist') }}" class="stt-studio-footlink">{{ $theme['footer_link_wishlist'] ?? 'Wishlist' }}</a>
@endif</li>
                    <li><a href="{{ url('/sitemap.xml') }}" class="stt-studio-footlink">{{ $theme['footer_link_sitemap'] ?? 'Sitemap' }}</a></li>
                </ul>
            </div>

            {{-- Shop --}}
            <div>
                <p class="stt-studio-footlabel">{{ $theme['footer_shop_heading'] ?? 'Shop' }}</p>
                <ul class="mt-5 space-y-2.5">
                    <li><a href="{{ url('/shop') }}" class="stt-studio-footlink">{{ $theme['footer_link_all_products'] ?? 'All products' }}</a></li>
                    @foreach ($tree as $category)
                        <li><a href="{{ url('/category/' . $category->slug) }}" class="stt-studio-footlink">{{ $category->name }}</a></li>
                    @endforeach
                </ul>
            </div>

            {{-- Contact + social --}}
            <div>
                <p class="stt-studio-footlabel">{{ $theme['footer_contact_heading'] ?? 'Contact us' }}</p>
                <p class="mt-5 text-sm leading-relaxed" style="color: rgba(255,255,255,.7)">{{ $theme['footer_text'] ?? 'Classic fashion staples, made to be worn on repeat.' }}</p>
                <ul class="mt-4 space-y-2.5">
                    @if ($storeEmail)<li><a href="mailto:{{ $storeEmail }}" class="stt-studio-footlink">{{ $storeEmail }}</a></li>@endif
                    @if ($storePhone)<li><a href="tel:{{ preg_replace('/[^0-9+]/', '', $storePhone) }}" class="stt-studio-footlink">{{ $storePhone }}</a></li>@endif
                </ul>
                @if ($facebook || $instagram || $whatsapp)
                    <div class="mt-5 flex items-center gap-2" aria-label="{{ __('storefront.social_media') }}">
                        @if ($facebook)
                            <a href="{{ $facebook }}" target="_blank" rel="noopener" aria-label="Facebook" class="stt-studio-social">
                                <svg viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4"><path d="M13.5 21v-7h2.4l.5-3h-2.9V9.1c0-.9.3-1.6 1.7-1.6h1.3V4.8c-.6-.1-1.5-.2-2.5-.2-2.4 0-4 1.5-4 4.1V11H7.5v3H10v7h3.5Z"/></svg>
                            </a>
                        @endif
                        @if ($instagram)
                            <a href="{{ $instagram }}" target="_blank" rel="noopener" aria-label="Instagram" class="stt-studio-social">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" class="h-4 w-4"><rect x="3.5" y="3.5" width="17" height="17" rx="4.5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1" fill="currentColor" stroke="none"/></svg>
                            </a>
                        @endif
                        @if ($whatsapp)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}" target="_blank" rel="noopener" aria-label="WhatsApp" class="stt-studio-social">
                                <svg viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.71.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c0-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        @if ($showNewsletter)
            <div class="mt-14 max-w-xl" style="border-top: 1px solid rgba(255,255,255,.14); padding-top: 2.5rem">
                <livewire:marketing.newsletter-form
                    :heading="$theme['footer_newsletter_heading'] ?? 'Subscribe to our newsletter'"
                    :subheading="$theme['footer_newsletter_subheading'] ?? 'New arrivals, offers and style notes — straight to your inbox.'" />
            </div>
        @endif

        @if ($showPaymentIcons)
            <div class="mt-12 flex flex-wrap items-center gap-2" aria-label="{{ __('storefront.accepted_payment_methods') }}">
                @foreach ($paymentMethods as $method)
                    <span class="px-2.5 py-1.5 text-[10px] font-bold uppercase" style="letter-spacing: 0.1em; border: 1px solid rgba(255,255,255,.25); border-radius: var(--st-radius); color: rgba(255,255,255,.7)">{{ $method }}</span>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Thin copyright bar --}}
    <div style="border-top: 1px solid rgba(255,255,255,.14)">
        <div class="st-container flex flex-col items-center justify-between gap-2 py-5 text-xs sm:flex-row" style="color: rgba(255,255,255,.6)">
            <p>&copy; {{ date('Y') }} {{ $storeName }}. {{ $theme['footer_copyright'] ?? 'All rights reserved.' }}</p>
            <p>{{ $theme['footer_powered_by'] ?? 'Powered by Shopcrafty' }}</p>
        </div>
    </div>
</footer>
