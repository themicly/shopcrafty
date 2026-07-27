@php
    $banners = \Themicly\Shopcrafty\Modules\Themes\Models\Banner::live()->placement('home_slider')
        ->where(fn ($q) => $q->whereNull('theme_id')->orWhere('theme_id', app(\Themicly\Shopcrafty\Modules\Themes\Services\ThemeService::class)->active()?->id))
        ->orderBy('sort')->get();

    $align = ($s['align'] ?? 'left') === 'center' ? 'center' : 'left';
    $heightClass = match ($s['height'] ?? 'standard') {
        'compact' => 'h-[46vh] min-h-[300px] max-h-[520px]',
        'tall' => 'h-[82vh] min-h-[520px] max-h-[840px]',
        default => 'h-[62vh] min-h-[400px] max-h-[680px]',
    };
    // Alignment-aware scrim + content positioning.
    $scrim = $align === 'center'
        ? 'bg-black/35'
        : 'bg-gradient-to-r from-black/60 via-black/25 to-transparent';
    $contentAlign = $align === 'center' ? 'items-center text-center' : 'items-start text-left';
@endphp

@if ($banners->isNotEmpty())
    <section class="st-reveal"
        x-data="{
            i: 0,
            n: {{ $banners->count() }},
            autoplay: {{ ($s['autoplay'] ?? true) ? 'true' : 'false' }},
            timer: null,
            reduce: window.matchMedia('(prefers-reduced-motion: reduce)').matches,
            init() { this.start(); },
            go(k) { this.i = (k + this.n) % this.n; this.start(); },
            next() { this.go(this.i + 1); },
            prev() { this.go(this.i - 1); },
            start() {
                this.stop();
                if (!this.autoplay || this.n < 2 || this.reduce) return;
                this.timer = setInterval(() => { this.i = (this.i + 1) % this.n; }, 6000);
            },
            stop() { if (this.timer) clearInterval(this.timer); },
        }"
        @mouseenter="stop()" @mouseleave="start()"
        @keydown.window.arrow-left="prev()" @keydown.window.arrow-right="next()">

        <div class="group relative overflow-hidden">
            {{-- Track --}}
            <div class="flex transition-transform duration-700 ease-[cubic-bezier(.4,0,.2,1)]" :style="`transform: translateX(-${i * 100}%)`">
                @foreach ($banners as $idx => $banner)
                    @php $bannerLink = preg_match('#^(https?://|/|\#|mailto:|tel:)#i', (string) $banner->link_url) ? $banner->link_url : '#'; @endphp
                    <div class="relative w-full shrink-0 {{ $heightClass }}">
                        <a href="{{ $bannerLink }}" class="absolute inset-0 block" @if ($bannerLink === '#') @click.prevent @endif aria-label="{{ $banner->title ?: __('storefront.slide_number', ['number' => $idx + 1]) }}">
                            <picture>
                                <source media="(max-width: 640px)" srcset="{{ $banner->mobileImage() }}">
                                <img src="{{ $banner->image_large }}" alt="{{ $banner->title }}" class="h-full w-full object-cover" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                            </picture>
                        </a>

                        <div class="pointer-events-none absolute inset-0 {{ $scrim }}"></div>

                        @if ($banner->title || $banner->subtitle || $banner->link_label)
                            <div class="pointer-events-none absolute inset-0 flex flex-col justify-center {{ $contentAlign }} px-6 py-10 sm:px-12 lg:px-16">
                                <div class="max-w-xl {{ $align === 'center' ? 'mx-auto' : '' }}">
                                    @if ($banner->title)
                                        <h2 class="st-display text-3xl font-semibold leading-[1.08] text-white drop-shadow-sm sm:text-5xl lg:text-6xl">{{ $banner->title }}</h2>
                                    @endif
                                    @if ($banner->subtitle)
                                        <p class="mt-4 max-w-md text-sm text-white/90 sm:text-lg {{ $align === 'center' ? 'mx-auto' : '' }}">{{ $banner->subtitle }}</p>
                                    @endif
                                    @if ($banner->link_label)
                                        <span class="pointer-events-auto mt-7 inline-flex items-center gap-2 px-7 py-3.5 text-sm font-semibold shadow-lg transition-transform duration-200 hover:-translate-y-0.5"
                                            style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius-sm)">
                                            {{ $banner->link_label }}
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16m0 0-6-6m6 6-6 6"/></svg>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            @if ($banners->count() > 1)
                {{-- Prev / next arrows (reveal on hover for desktop, always tappable on touch) --}}
                <button type="button" @click="prev()" aria-label="{{ __('storefront.previous_slide') }}"
                    class="absolute start-3 top-1/2 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full bg-white/25 text-white backdrop-blur transition hover:bg-white/40 focus-visible:opacity-100 sm:start-5 sm:opacity-0 sm:group-hover:opacity-100">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5 rtl:rotate-180"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
                </button>
                <button type="button" @click="next()" aria-label="{{ __('storefront.next_slide') }}"
                    class="absolute end-3 top-1/2 grid h-11 w-11 -translate-y-1/2 place-items-center rounded-full bg-white/25 text-white backdrop-blur transition hover:bg-white/40 focus-visible:opacity-100 sm:end-5 sm:opacity-0 sm:group-hover:opacity-100">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-5 w-5 rtl:rotate-180"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                </button>

                {{-- Dots (active one elongates) --}}
                <div class="absolute bottom-5 left-1/2 flex -translate-x-1/2 items-center gap-2">
                    @foreach ($banners as $idx => $banner)
                        <button type="button" @click="go({{ $idx }})" aria-label="{{ __('storefront.go_to_slide', ['number' => $idx + 1]) }}"
                            class="h-1.5 rounded-full transition-all duration-300"
                            :class="i === {{ $idx }} ? 'w-9 bg-white' : 'w-2.5 bg-white/55'"></button>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endif
