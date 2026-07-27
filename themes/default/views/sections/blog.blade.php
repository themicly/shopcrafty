{{-- Blog posts populate once the CMS lands (Session 11). Renders nothing until then. --}}
@php
    $posts = \Illuminate\Support\Facades\Schema::hasTable('cms_posts')
        ? \Illuminate\Support\Facades\DB::table('cms_posts')->where('status', 'published')->latest('published_at')->limit(3)->get()
        : collect();
@endphp

@if ($posts->isNotEmpty())
    <section class="py-16 sm:py-24" style="background: var(--st-bg)">
        <div class="st-container">
            <div class="st-reveal mb-10 flex flex-wrap items-end justify-between gap-4 sm:mb-14">
                <div>
                    <x-st.section-heading :eyebrow="__('storefront.journal')" :title="$s['heading'] ?? 'From the blog'" />
                    <span aria-hidden="true" style="display: block; width: 3rem; height: 3px; margin-top: 0.875rem; border-radius: 999px; background: linear-gradient(90deg, var(--st-primary), var(--st-accent))"></span>
                </div>
                <a href="{{ url('/blog') }}" class="stt-aurora-viewall hidden text-sm font-semibold sm:inline-flex">{{ __('storefront.all_posts') }}</a>
            </div>
            <div class="grid gap-6 sm:grid-cols-3">
                @foreach ($posts as $post)
                    <a href="{{ url('/blog/' . $post->slug) }}" class="st-reveal group flex flex-col overflow-hidden border transition-transform hover:-translate-y-0.5"
                        style="border-color: var(--st-line); border-radius: var(--st-radius); background: var(--st-bg)">
                        <span class="block aspect-[3/2] w-full overflow-hidden" style="background: var(--st-surface)">
                            @if (! empty($post->featured_image))
                                <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" loading="lazy" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105">
                            @else
                                <span class="grid h-full w-full place-items-center" aria-hidden="true"
                                    style="background: linear-gradient(135deg, color-mix(in srgb, var(--st-primary) 16%, var(--st-bg)), color-mix(in srgb, var(--st-accent) 12%, var(--st-bg)))">
                                    <span class="st-display text-3xl font-semibold" style="color: color-mix(in srgb, var(--st-primary) 45%, var(--st-bg))">{{ strtoupper(substr($post->title, 0, 1)) }}</span>
                                </span>
                            @endif
                        </span>
                        <span class="flex flex-1 flex-col p-6">
                            @if (! empty($post->published_at))
                                <time datetime="{{ \Illuminate\Support\Carbon::parse($post->published_at)->toDateString() }}" class="text-xs font-medium uppercase tracking-[0.14em]" style="color: var(--st-ink-soft)">
                                    {{ \Illuminate\Support\Carbon::parse($post->published_at)->isoFormat('MMM D, YYYY') }}
                                </time>
                            @endif
                            <span class="st-display mt-2 text-lg font-semibold leading-snug" style="color: var(--st-ink)">{{ $post->title }}</span>
                            @if (! empty($post->excerpt))
                                <span class="mt-2 line-clamp-2 text-sm leading-relaxed" style="color: var(--st-ink-soft)">{{ $post->excerpt }}</span>
                            @endif
                            <span class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold" style="color: var(--st-accent)">
                                {{ __('storefront.read_more') }}
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 12h16m0 0-6-6m6 6-6 6"/></svg>
                            </span>
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif
