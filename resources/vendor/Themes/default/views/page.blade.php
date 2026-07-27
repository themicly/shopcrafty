@extends('theme::layout')

@section('title', $page->seo_title ?: $page->title)
@section('meta_description', $page->seo_description ?: '')

@section('content')
    @if ($page->template !== 'blank' && empty($page->blocks))
        <div class="st-container py-12">
            <h1 class="st-display text-3xl font-semibold" style="color: var(--st-ink)">{{ $page->title }}</h1>
        </div>
    @endif

    @foreach ($page->blocks ?? [] as $block)
        @includeIf('theme::blocks.' . $block['type'], ['b' => $block['settings'] ?? []])
    @endforeach
@endsection
