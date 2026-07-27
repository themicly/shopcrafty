@php
    use Illuminate\Support\Facades\Route;
    $storeName = settings('general.store_name', config('app.name'));
    $whatsapp = settings('general.whatsapp');
    $tree = app(\Themicly\Shopcrafty\Modules\Catalog\Contracts\CategoryTree::class)->roots()->take(6);
    $footerMenu = \Themicly\Shopcrafty\Modules\CMS\Models\Menu::where('location', 'footer')->first()?->items()->get() ?? collect();

    // Footer builder options (TASK #31).
    $showPaymentIcons = (bool) ($theme['footer_show_payment_icons'] ?? true);
    $paymentMethods = collect(preg_split('/[,\\r\\n]+/', (string) ($theme['footer_payment_methods'] ?? '')))->map(fn ($method) => trim($method))->filter()->unique()->values();
    $showNewsletter = (bool) ($theme['footer_newsletter'] ?? false) && settings('marketing.newsletter_enabled', true);
@endphp

{{-- Marketplace footer: light, dense, service-strip + mega columns — a soft surface band
     framed by hairlines, keeping the storefront airy end to end (no dark chrome). --}}
<footer class="mt-16" style="background: var(--st-surface); color: var(--st-ink); border-top: 1px solid var(--st-line)">
    {{-- Top USP / trust reminder rule --}}
    <div style="border-bottom: 1px solid var(--st-line)">
        <div class="st-container grid grid-cols-2 gap-6 py-8 md:grid-cols-4">
            @foreach ([
                ['🚚', $theme['footer_usp1'] ?? 'Next-day delivery', $theme['footer_usp1_sub'] ?? 'On thousands of items'],
                ['🛡️', $theme['footer_usp2'] ?? '2-year warranty', $theme['footer_usp2_sub'] ?? 'On all electronics'],
                ['↩️', $theme['footer_usp3'] ?? '30-day returns', $theme['footer_usp3_sub'] ?? 'Hassle-free'],
                ['🔒', $theme['footer_usp4'] ?? 'Secure checkout', $theme['footer_usp4_sub'] ?? 'Encrypted payments'],
            ] as [$icon, $title, $sub])
                <div class="flex items-start gap-3">
                    <span class="text-2xl">{{ $icon }}</span>
                    <div>
                        <p class="text-sm font-bold" style="color: var(--st-ink)">{{ $title }}</p>
                        <p class="text-xs" style="color: var(--st-ink-soft)">{{ $sub }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="st-container grid gap-10 py-14 sm:grid-cols-2 lg:grid-cols-5">
        <div class="lg:col-span-2">
            <p class="st-display text-2xl font-extrabold" style="color: var(--st-ink)">{{ $storeName }}</p>
            <p class="mt-3 max-w-sm text-sm leading-relaxed" style="color: var(--st-ink-soft)">{{ $theme['footer_text'] ?? 'Tech that works.' }}</p>

            @if ($showNewsletter)
                {{-- Newsletter box: squared input + submit on the light band --}}
                <div class="mt-6 max-w-sm">
                    <p class="text-xs font-bold uppercase tracking-wider" style="color: var(--st-ink)">{{ $theme['footer_newsletter_heading'] ?? 'Get deals in your inbox' }}</p>
                    <livewire:marketing.newsletter-form heading="" subheading="" />
                </div>
            @else
                <form action="{{ route('storefront.search') }}" method="GET" class="mt-6 flex max-w-sm overflow-hidden" style="border-radius: var(--st-radius); background: var(--st-bg); border: 1px solid var(--st-line)">
                    <input name="q" aria-label="{{ __('storefront.search_products') }}" placeholder="{{ $theme['footer_search_placeholder'] ?? 'Find a product…' }}" class="h-10 flex-1 bg-transparent px-3 text-sm focus:outline-none" style="color: var(--st-ink)">
                    <button class="px-4 text-xs font-bold uppercase tracking-wide" style="background: var(--st-primary); color: var(--st-primary-ink)">{{ $theme['footer_search_button'] ?? 'Go' }}</button>
                </form>
            @endif
        </div>

        <div>
            <p class="text-xs font-bold uppercase tracking-wider" style="color: var(--st-ink)">{{ $theme['footer_col_departments'] ?? 'Departments' }}</p>
            <ul class="mt-4 space-y-2.5 text-sm">
                <li><a href="{{ url('/shop') }}" class="hover:opacity-70">{{ $theme['footer_all_products'] ?? 'All products' }}</a></li>
                @foreach ($tree as $category)
                    <li><a href="{{ url('/category/' . $category->slug) }}" class="hover:opacity-70">{{ $category->name }}</a></li>
                @endforeach
            </ul>
        </div>

        <div>
            <p class="text-xs font-bold uppercase tracking-wider" style="color: var(--st-ink)">{{ $theme['footer_col_support'] ?? 'Support' }}</p>
            <ul class="mt-4 space-y-2.5 text-sm">
                @foreach ($footerMenu as $item)
                    <li><a href="{{ $item->url }}" class="hover:opacity-70">{{ $item->label }}</a></li>
                @endforeach
                <li><a href="{{ url('/track') }}" class="hover:opacity-70">{{ $theme['footer_track'] ?? 'Track order' }}</a></li>
                @if ($whatsapp)
                    <li><a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsapp) }}" class="hover:opacity-70">{{ $theme['footer_whatsapp'] ?? 'WhatsApp us' }}</a></li>
                @endif
            </ul>
        </div>

        <div>
            <p class="text-xs font-bold uppercase tracking-wider" style="color: var(--st-ink)">{{ $theme['footer_col_company'] ?? 'Company' }}</p>
            <ul class="mt-4 space-y-2.5 text-sm">
                <li><a href="{{ url('/pages/about') }}" class="hover:opacity-70">{{ $theme['footer_about'] ?? 'About us' }}</a></li>
                @if (app(\Themicly\Shopcrafty\Core\Module\AddonRegistry::class)->installed('blog'))<li><a href="{{ url('/blog') }}" class="hover:opacity-70">{{ $theme['footer_blog'] ?? 'Blog' }}</a></li>@endif
                <li><a href="{{ url('/sitemap.xml') }}" class="hover:opacity-70">{{ $theme['footer_sitemap'] ?? 'Sitemap' }}</a></li>
            </ul>
        </div>
    </div>

    {{-- Bottom strip: payment-method chips · copyright · tagline --}}
    <div style="border-top: 1px solid var(--st-line)">
        <div class="st-container flex flex-col items-center gap-4 py-6 text-xs sm:flex-row sm:justify-between" style="color: var(--st-ink-soft)">
            <p>&copy; {{ date('Y') }} {{ $storeName }}. {{ $theme['footer_copyright'] ?? 'All rights reserved.' }}</p>
            @if ($showPaymentIcons)
                <div class="flex flex-wrap items-center justify-center gap-1.5" aria-label="{{ __('storefront.accepted_payment_methods') }}">
                    @foreach ($paymentMethods as $method)
                        <span class="px-2 py-1 text-[11px] font-semibold" style="border: 1px solid var(--st-line); background: var(--st-bg); border-radius: var(--st-radius); color: var(--st-ink)">{{ $method }}</span>
                    @endforeach
                </div>
            @endif
            <a href="https://themicly.com/shopcrafty" target="_blank" rel="noopener noreferrer"
                class="transition hover:opacity-70" aria-label="Powered by Shopcrafty">Powered by Shopcrafty</a>
        </div>
    </div>
</footer>
