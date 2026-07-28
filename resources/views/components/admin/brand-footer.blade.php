@php
    $shopcraftyUrl = 'https://themicly.com/shopcrafty';
    $themiclyUrl = 'https://themicly.com';
    $githubUrl = 'https://github.com/themicly/shopcrafty';
@endphp

<footer class="mt-auto flex flex-wrap items-center justify-end gap-x-5 gap-y-2 border-t border-line pt-4 text-xs text-content-muted">
    <a href="{{ $shopcraftyUrl }}" target="_blank" rel="noopener" class="hover:text-primary">Powered by <span class="font-medium text-content-secondary">Shopcrafty</span></a>
    <a href="{{ $themiclyUrl }}" target="_blank" rel="noopener" class="hover:text-primary">Themicly</a>
    <a href="{{ $githubUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 hover:text-primary" aria-label="Give Shopcrafty a star on GitHub">
        <svg viewBox="0 0 24 24" fill="currentColor" class="h-3.5 w-3.5" aria-hidden="true"><path d="m12 2.7 2.83 5.74 6.34.92-4.59 4.47 1.08 6.31L12 17.16l-5.66 2.98 1.08-6.31-4.59-4.47 6.34-.92L12 2.7Z"/></svg>
        Give a star on GitHub
    </a>
</footer>
