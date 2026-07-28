@php
    $shopcraftyVersion = 'dev-main';

    if (class_exists(\Composer\InstalledVersions::class) && \Composer\InstalledVersions::isInstalled('themicly/shopcrafty')) {
        $shopcraftyVersion = \Composer\InstalledVersions::getPrettyVersion('themicly/shopcrafty') ?: $shopcraftyVersion;
    }
@endphp

<div class="bz-sidebar-label border-t border-line px-3 pb-1 pt-3 text-[10px] leading-4 text-content-muted">
    Shopcrafty v{{ $shopcraftyVersion }}
</div>
