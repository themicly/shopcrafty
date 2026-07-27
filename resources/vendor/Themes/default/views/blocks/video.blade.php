@php
    // Only render embeds from a known allow-list; anything else is refused so a
    // staff-authored data:/arbitrary iframe src can't be framed (CMS-03).
    $rawUrl = $b['url'] ?? '';
    $embedUrl = '';

    if (preg_match('~youtube\.com/watch\?v=([A-Za-z0-9_-]+)~', $rawUrl, $m) || preg_match('~youtu\.be/([A-Za-z0-9_-]+)~', $rawUrl, $m)) {
        $embedUrl = 'https://www.youtube.com/embed/' . $m[1];
    } elseif (preg_match('~vimeo\.com/(\d+)~', $rawUrl, $m)) {
        $embedUrl = 'https://player.vimeo.com/video/' . $m[1];
    }
@endphp

@if (! empty($embedUrl))
    <section class="st-reveal py-10 sm:py-16" style="background: var(--st-bg)">
        <div class="st-container">
            <div class="mx-auto max-w-4xl">
                <div class="aspect-video w-full overflow-hidden" style="border-radius: var(--st-radius)">
                    <iframe src="{{ $embedUrl }}" class="h-full w-full" frameborder="0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </section>
@endif
