@extends('theme::layout')

@section('title', ($code ?? 'Error').' — '.settings('general.store_name', config('app.name')))

@section('content')
    <section class="st-container flex min-h-[60vh] flex-col items-center justify-center py-20 text-center">
        <p class="st-display text-6xl font-semibold sm:text-8xl" style="color: var(--st-primary)">{{ $code ?? '' }}</p>
        <h1 class="st-display mt-4 text-2xl font-semibold sm:text-3xl" style="color: var(--st-ink)">{{ $title ?? 'Something went wrong' }}</h1>
        <p class="mt-3 max-w-md text-sm" style="color: var(--st-ink-soft)">{{ $message ?? 'An unexpected error occurred.' }}</p>
        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <a href="{{ url('/') }}" class="rounded-full px-5 py-2.5 text-sm font-semibold" style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius)">Back to shop</a>
            <a href="{{ route('storefront.shop') }}" class="rounded-full px-5 py-2.5 text-sm font-semibold" style="border: 1px solid var(--st-line); color: var(--st-ink); border-radius: var(--st-radius)">Browse products</a>
        </div>
    </section>
@endsection
