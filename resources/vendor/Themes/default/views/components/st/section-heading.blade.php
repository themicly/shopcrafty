@props([
    'eyebrow' => null,
    'title',
    'align' => 'left',
])

<div class="st-reveal {{ $align === 'center' ? 'text-center' : '' }} {{ $attributes->get('class') }}">
    @if ($eyebrow)
        <p class="mb-2 text-xs font-semibold uppercase tracking-[0.18em]" style="color: var(--st-accent)">{{ $eyebrow }}</p>
    @endif
    <h2 class="st-display text-3xl font-semibold sm:text-4xl" style="color: var(--st-ink)">{{ $title }}</h2>
    @isset($slot)
        <div class="mt-2 text-sm" style="color: var(--st-ink-soft)">{{ $slot }}</div>
    @endisset
</div>
