@props([
    'type',
    'large' => false,
    'fill' => false,   // stretch to the parent box (e.g. an aspect-square card)
])

@php
    $box = $fill ? 'h-full w-full' : ($large ? 'h-28 w-full' : 'h-14 w-24');
    // In fill mode the surrounding card supplies the frame — a second border
    // here reads as a double outline, so the preview stays flat.
    $frame = $fill ? '' : ' border border-line';
@endphp

{{-- A tiny wireframe of how the section appears on the storefront. --}}
<div {{ $attributes->merge(['class' => $box.' shrink-0 overflow-hidden rounded-md bg-surface p-1.5'.$frame]) }} aria-hidden="true">
    @switch($type)
        @case('banners')
            <div class="h-full w-full rounded-sm bg-primary/25"></div>
            @break

        @case('hero')
            <div class="flex h-full w-full flex-col justify-center gap-1 rounded-sm bg-surface-sunken px-2">
                <div class="h-1.5 w-3/4 rounded-full bg-content/30"></div>
                <div class="h-1 w-1/2 rounded-full bg-content/15"></div>
                <div class="mt-0.5 h-1.5 w-1/4 rounded-sm bg-primary/50"></div>
            </div>
            @break

        @case('featured_products')
        @case('flash_sale')
            <div class="flex h-full w-full flex-col gap-1">
                <div class="h-1 w-1/2 rounded-full bg-content/20"></div>
                <div class="flex flex-1 gap-1">
                    @for ($i = 0; $i < 4; $i++)<div class="flex-1 rounded-sm bg-surface-sunken"></div>@endfor
                </div>
            </div>
            @break

        @case('brands')
            <div class="flex h-full w-full items-center justify-center gap-1.5">
                @for ($i = 0; $i < 4; $i++)<div class="h-4 w-4 rounded-full bg-surface-sunken"></div>@endfor
            </div>
            @break

        @case('categories')
            <div class="flex h-full w-full flex-col gap-1">
                <div class="h-1 w-1/3 rounded-full bg-content/20"></div>
                <div class="grid flex-1 grid-cols-4 gap-1">
                    @for ($i = 0; $i < 4; $i++)<div class="rounded-sm bg-surface-sunken"></div>@endfor
                </div>
            </div>
            @break

        @case('usp')
            <div class="flex h-full w-full items-center justify-between gap-1.5 px-1">
                @for ($i = 0; $i < 4; $i++)
                    <div class="flex flex-1 items-center gap-1">
                        <div class="h-3 w-3 shrink-0 rounded-full bg-primary/40"></div>
                        <div class="h-1 flex-1 rounded-full bg-content/15"></div>
                    </div>
                @endfor
            </div>
            @break

        @case('feature')
            <div class="flex h-full w-full items-center gap-1.5">
                <div class="flex flex-1 flex-col justify-center gap-1">
                    <div class="h-1.5 w-3/4 rounded-full bg-content/30"></div>
                    <div class="h-1 w-full rounded-full bg-content/15"></div>
                    <div class="h-1 w-2/3 rounded-full bg-content/15"></div>
                    <div class="mt-0.5 h-1.5 w-1/3 rounded-sm bg-primary/50"></div>
                </div>
                <div class="h-full w-2/5 rounded-sm bg-surface-sunken"></div>
            </div>
            @break

        @case('instagram')
            <div class="grid h-full w-full grid-cols-6 gap-0.5">
                @for ($i = 0; $i < 6; $i++)<div class="aspect-square rounded-[2px] bg-surface-sunken"></div>@endfor
            </div>
            @break

        @case('newsletter')
            <div class="flex h-full w-full flex-col items-center justify-center gap-1 rounded-sm bg-surface-sunken">
                <div class="h-1 w-1/2 rounded-full bg-content/25"></div>
                <div class="flex w-3/4 gap-0.5">
                    <div class="h-2 flex-1 rounded-sm bg-surface"></div>
                    <div class="h-2 w-4 rounded-sm bg-primary/50"></div>
                </div>
            </div>
            @break

        @default
            <div class="flex h-full w-full flex-col justify-center gap-1">
                <div class="h-1 w-full rounded-full bg-content/15"></div>
                <div class="h-1 w-2/3 rounded-full bg-content/15"></div>
            </div>
    @endswitch
</div>
