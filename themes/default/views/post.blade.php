@extends('theme::layout')

@section('title', $post->seo_title ?: $post->title)
@section('meta_description', $post->seo_description ?: \Illuminate\Support\Str::limit(strip_tags($post->excerpt), 150))
@if ($post->featured_image)@section('og_image', $post->featured_image)@endif

@section('content')
    <article class="st-container py-12">
        <div class="mx-auto max-w-2xl">
            <p class="text-sm" style="color: var(--st-ink-soft)">{{ $post->published_at?->format('M j, Y') }}@if ($post->category) · {{ $post->category->name }}@endif</p>
            <h1 class="st-display mt-2 text-3xl font-semibold sm:text-4xl" style="color: var(--st-ink)">{{ $post->title }}</h1>
        </div>

        @if ($post->featured_image)
            <div class="mx-auto mt-8 max-w-3xl overflow-hidden" style="border-radius: var(--st-radius)">
                <img src="{{ $post->featured_image }}" alt="{{ $post->title }}" class="w-full object-cover">
            </div>
        @endif

        <div class="mx-auto mt-8 max-w-2xl text-base leading-relaxed" style="color: var(--st-ink)">
            @foreach ($post->blocks ?? [] as $block)
                @if (($block['type'] ?? '') === 'text')
                    {!! nl2br(e($block['settings']['body'] ?? '')) !!}
                @else
                    @includeIf('theme::blocks.' . $block['type'], ['b' => $block['settings'] ?? []])
                @endif
            @endforeach
        </div>
    </article>
@endsection
