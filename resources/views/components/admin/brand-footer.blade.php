@php
    $shopcraftyUrl = 'https://themicly.com/shopcrafty';
    $shopcraftyVersion = 'dev-main';

    if (class_exists(\Composer\InstalledVersions::class) && \Composer\InstalledVersions::isInstalled('themicly/shopcrafty')) {
        $shopcraftyVersion = \Composer\InstalledVersions::getPrettyVersion('themicly/shopcrafty') ?: $shopcraftyVersion;
    }
@endphp

<div class="bz-sidebar-label border-t border-line px-3 pb-1 pt-3 text-[10px] leading-4 text-content-muted">
    <p>Shopcrafty v{{ $shopcraftyVersion }}</p>
    <p>Powered by <a href="{{ $shopcraftyUrl }}" target="_blank" rel="noopener" class="font-medium text-content-secondary hover:text-primary">Themicly</a></p>
    <div class="mt-1 flex gap-2">
        <a href="{{ $shopcraftyUrl }}" target="_blank" rel="noopener" class="hover:text-primary">Shopcrafty</a>
        <span aria-hidden="true">·</span>
        <a href="{{ $shopcraftyUrl }}#rate-us" target="_blank" rel="noopener" class="hover:text-primary">Rate us</a>
    </div>
</div>
