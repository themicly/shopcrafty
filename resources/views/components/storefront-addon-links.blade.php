@php
    $features = app(\Themicly\Shopcrafty\Core\Module\AddonRegistry::class)->storefrontFeatures('footer');
    $features = collect($features)->filter(fn (array $feature) => ! empty($feature['route']) && Route::has($feature['route']));
@endphp

@if ($features->isNotEmpty())
    <nav class="mx-auto flex max-w-7xl flex-wrap items-center justify-center gap-x-5 gap-y-2 px-6 py-4 text-xs" style="color: var(--st-ink-soft)" aria-label="Store extensions">
        @foreach ($features as $feature)
            <a href="{{ route($feature['route']) }}" class="transition hover:underline">{{ $feature['label'] }}</a>
        @endforeach
    </nav>
@endif
