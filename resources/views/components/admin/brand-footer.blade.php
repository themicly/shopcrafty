@php
    $shopcraftyUrl = 'https://themicly.com/shopcrafty';
@endphp

<footer class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1 border-t border-line pt-4 text-xs text-content-muted">
    <span>Powered by <a href="{{ $shopcraftyUrl }}" target="_blank" rel="noopener" class="font-medium text-content-secondary hover:text-primary">Themicly</a></span>
    <span aria-hidden="true">·</span>
    <a href="{{ $shopcraftyUrl }}" target="_blank" rel="noopener" class="hover:text-primary">Shopcrafty</a>
    <span aria-hidden="true">·</span>
    <a href="{{ $shopcraftyUrl }}#rate-us" target="_blank" rel="noopener" class="hover:text-primary">Rate us</a>
</footer>
