@extends('theme::layout')

@section('title', __('storefront.blog'))

@section('content')
    <div class="st-container py-12 sm:py-16">
        <x-st.section-heading :eyebrow="__('storefront.journal')" :title="__('storefront.from_the_blog')" align="center" class="mb-10 sm:mb-14" />

        @if ($posts->isEmpty())
            <p class="py-16 text-center" style="color: var(--st-ink-soft)">{{ __('storefront.no_posts') }}</p>
        @else
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <a href="{{ route('storefront.post', $post->slug) }}" class="st-reveal group block">
                        @if ($post->featured_image)
                            <div class="mb-4 aspect-[3/2] overflow-hidden" style="border-radius: var(--st-radius); background: var(--st-surface)">
                                <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="h-full w-full object-cover transition group-hover:scale-105">
                            </div>
                        @endif
                        <p class="text-xs" style="color: var(--st-ink-soft)">{{ $post->published_at?->format('M j, Y') }}</p>
                        <h2 class="st-display mt-1 text-xl font-semibold" style="color: var(--st-ink)">{{ $post->title }}</h2>
                        @if ($post->excerpt)<p class="mt-2 text-sm" style="color: var(--st-ink-soft)">{{ \Illuminate\Support\Str::limit($post->excerpt, 120) }}</p>@endif
                    </a>
                @endforeach
            </div>
            <div class="mt-12">{{ $posts->links() }}</div>
        @endif
    </div>
@endsection
