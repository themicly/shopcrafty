@props([
    'title' => '',
    'description' => '',
    'url' => '',
    'baseUrl' => null,
    'image' => null,
])

@php
    $baseUrl = $baseUrl ?: url('/');
    $host = parse_url($baseUrl, PHP_URL_HOST) ?: 'example.com';
    $path = trim((string) $url, '/');

    $titleLen = mb_strlen((string) $title);
    $descLen = mb_strlen((string) $description);

    // Google typically truncates ~60 chars for titles, ~155 for descriptions.
    $tone = fn (int $len, int $ideal) => $len === 0
        ? 'text-content-muted'
        : ($len <= $ideal ? 'text-success' : 'text-warning');
@endphp

<div class="space-y-2">
    <div class="rounded-lg border border-line bg-surface p-4">
        <p class="mb-2 text-[11px] font-medium uppercase tracking-wide text-content-muted">Search result preview</p>

        <div class="flex gap-3">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-1 text-xs text-content-secondary">
                    <span>{{ $host }}</span>
                    @if ($path)<span>›</span><span class="truncate">{{ $path }}</span>@endif
                </div>
                <p class="mt-1 truncate text-lg leading-snug" style="color: #1a0dab">
                    {{ $title ?: 'Your title appears here' }}
                </p>
                <p class="mt-0.5 line-clamp-2 text-sm text-content-secondary">
                    {{ $description ?: 'Your meta description preview appears here — aim for a clear, compelling ~150 characters.' }}
                </p>
            </div>
            {{-- Rich-result thumbnail (Google shows the featured image beside the snippet). --}}
            @if ($image)
                <img src="{{ $image }}" alt="" class="h-16 w-16 shrink-0 rounded-lg border border-line object-cover">
            @endif
        </div>
    </div>

    <div class="flex flex-wrap gap-x-5 gap-y-1 text-xs">
        <span class="{{ $tone($titleLen, 60) }}">Title {{ $titleLen }}/60</span>
        <span class="{{ $tone($descLen, 155) }}">Description {{ $descLen }}/155</span>
    </div>
</div>
