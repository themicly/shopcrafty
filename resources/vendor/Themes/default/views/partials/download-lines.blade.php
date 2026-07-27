{{-- Renders purchased digital lines. Expects $lines (collection of arrays with
     name, order_number, license_key, files[]) and optional $showOrder. --}}
@php $showOrder = $showOrder ?? false; @endphp
<div class="space-y-4">
    @foreach ($lines as $line)
        <div class="border p-4" style="border-color: var(--st-line); border-radius: var(--st-radius-sm)">
            <div class="mb-3 flex flex-wrap items-baseline justify-between gap-2">
                <p class="font-semibold" style="color: var(--st-ink)">{{ $line['name'] }}</p>
                @if ($showOrder && ! empty($line['order_number']))
                    <span class="text-xs" style="color: var(--st-ink-soft)">{{ __('storefront.order_with_number', ['number' => $line['order_number']]) }}</span>
                @endif
            </div>

            @if (! empty($line['license_key']))
                <p class="mb-3 text-sm" style="color: var(--st-ink-soft)">
                    {{ __('storefront.license_key') }}
                    <span class="ml-1 font-mono font-medium" style="color: var(--st-ink)">{{ $line['license_key'] }}</span>
                </p>
            @endif

            <ul class="space-y-2">
                @foreach ($line['files'] as $file)
                    <li class="flex items-center justify-between gap-3">
                        <span class="min-w-0 flex-1 truncate text-sm" style="color: var(--st-ink)">
                            {{ $file['name'] }}
                            @if (! empty($file['size']))<span style="color: var(--st-ink-soft)"> · {{ $file['size'] }}</span>@endif
                        </span>
                        @if ($file['downloadable'])
                            <a href="{{ $file['url'] }}"
                                class="shrink-0 px-4 py-2 text-sm font-semibold"
                                style="background: var(--st-primary); color: var(--st-primary-ink); border-radius: var(--st-radius-sm)">{{ __('storefront.download') }}</a>
                        @else
                            <span class="shrink-0 text-xs" style="color: var(--st-ink-soft)">{{ __('storefront.download_limit_reached') }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    @endforeach
</div>
