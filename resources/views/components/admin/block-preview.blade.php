@props([
    'type',
    'fill' => false,   // stretch to the parent box (e.g. a drawer card)
])

@php
    $box = $fill ? 'h-full w-full' : 'h-14 w-24';
    // In fill mode the surrounding card supplies the frame — a second border
    // here reads as a double outline, so the preview stays flat.
    $frame = $fill ? '' : ' border border-line';
@endphp

{{-- A tiny wireframe of how the page block reads on the storefront. --}}
<div {{ $attributes->merge(['class' => $box.' shrink-0 overflow-hidden rounded-md bg-surface p-1.5'.$frame]) }} aria-hidden="true">
    @switch($type)
        @case('hero')
            <div class="flex h-full w-full flex-col items-center justify-center gap-1 rounded-sm bg-surface-sunken px-2">
                <div class="h-1.5 w-3/4 rounded-full bg-content/30"></div>
                <div class="h-1 w-1/2 rounded-full bg-content/15"></div>
                <div class="mt-0.5 h-2 w-1/4 rounded-sm bg-primary/50"></div>
            </div>
            @break

        @case('text')
            <div class="flex h-full w-full flex-col justify-center gap-1 px-1">
                <div class="h-1 w-full rounded-full bg-content/20"></div>
                <div class="h-1 w-11/12 rounded-full bg-content/20"></div>
                <div class="h-1 w-full rounded-full bg-content/20"></div>
                <div class="h-1 w-2/3 rounded-full bg-content/20"></div>
            </div>
            @break

        @case('image')
            <div class="grid h-full w-full place-items-center rounded-sm bg-surface-sunken">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.2" stroke="currentColor" class="h-1/2 w-1/2 text-content/25"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M18 15.75h.008M6 20.25h12A2.25 2.25 0 0 0 20.25 18V6A2.25 2.25 0 0 0 18 3.75H6A2.25 2.25 0 0 0 3.75 6v12A2.25 2.25 0 0 0 6 20.25Z" /></svg>
            </div>
            @break

        @case('gallery')
            <div class="grid h-full w-full grid-cols-2 gap-1">
                @for ($i = 0; $i < 4; $i++)<div class="rounded-sm bg-surface-sunken"></div>@endfor
            </div>
            @break

        @case('video')
            <div class="grid h-full w-full place-items-center rounded-sm bg-content/70">
                <span class="grid h-1/3 w-1/3 place-items-center rounded-full bg-surface/90">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="h-1/2 w-1/2 translate-x-px text-content/70"><path d="M8 5.5v13l11-6.5-11-6.5Z"/></svg>
                </span>
            </div>
            @break

        @case('faq')
            <div class="flex h-full w-full flex-col justify-center gap-1">
                @for ($i = 0; $i < 3; $i++)
                    <div class="flex items-center justify-between rounded-sm border border-line px-1 py-0.5">
                        <span class="h-1 w-2/3 rounded-full bg-content/25"></span>
                        <span class="h-1 w-1 rounded-full bg-content/25"></span>
                    </div>
                @endfor
            </div>
            @break

        @case('cta')
            <div class="flex h-full w-full flex-col items-center justify-center gap-1 rounded-sm bg-primary/20 px-2">
                <div class="h-1.5 w-2/3 rounded-full bg-content/35"></div>
                <div class="mt-0.5 h-2 w-1/3 rounded-sm bg-primary/60"></div>
            </div>
            @break

        @case('products')
            <div class="flex h-full w-full flex-col gap-1">
                <div class="h-1 w-1/2 rounded-full bg-content/20"></div>
                <div class="flex flex-1 gap-1">
                    @for ($i = 0; $i < 4; $i++)
                        <div class="flex flex-1 flex-col gap-0.5">
                            <div class="flex-1 rounded-sm bg-surface-sunken"></div>
                            <div class="h-0.5 w-full rounded-full bg-content/20"></div>
                        </div>
                    @endfor
                </div>
            </div>
            @break

        @default
            <div class="h-full w-full rounded-sm bg-surface-sunken"></div>
    @endswitch
</div>
