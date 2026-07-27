@props([
    'printable' => false,
    'title' => null,   // print header title (only used when printable)
    'flush' => false,  // omit the card-like shell when already inside a ui.card component
])

{{--
    Styled table shell. Compose plain <thead>/<tbody>/<tr>/<th>/<td> inside.
    Mirrors the Sellicly look: soft shadow card, 2xl corners, an 11px uppercase
    header band, generous py-3 px-4 cells, muted row text, and a subtle hover.

    Pass `printable` (and optionally `title`) to make the browser Print action
    ("window.print()") output just this table's structured data — the admin
    chrome is dropped by the `.bz-print-area` rules in app.css. Mark non-data
    columns (selection checkboxes, action buttons) with `print:hidden` on BOTH
    the <th> and the matching <td> so they fall away cleanly on paper.

    Pass `flush` when nesting inside an <x-ui.card>: the card already supplies
    a border/shadow/rounded corners/background, so this table's own copy of
    the same would double up into a visible "box inside a box".
--}}
<div @class([
    'overflow-x-auto',
    'rounded-2xl border border-line bg-surface-raised shadow-sm' => ! $flush,
    'bz-print-area' => $printable,
])>
    @if ($printable)
        <div class="bz-print-only px-4 pt-4">
            @if ($title)
                <h1 class="bz-print-title">{{ $title }}</h1>
            @endif
            <p class="bz-print-meta">{{ settings('general.store_name', config('app.name')) }} · printed {{ now()->format('j M Y, H:i') }}</p>
        </div>
    @endif
    <table {{ $attributes->merge([
        'class' => 'w-full text-left text-sm '
            . '[&_thead]:border-b [&_thead]:border-line [&_thead_tr]:bg-surface-sunken/40 '
            . '[&_th]:px-4 [&_th]:py-3 [&_th]:text-[11px] [&_th]:font-semibold [&_th]:uppercase [&_th]:tracking-wider [&_th]:text-content-muted [&_th]:whitespace-nowrap '
            . '[&_td]:px-4 [&_td]:py-3 [&_td]:text-content-secondary [&_td]:align-middle [&_td]:tabular-nums '
            . '[&_tbody_tr]:border-t [&_tbody_tr]:border-line/70 [&_tbody_tr]:transition-colors '
            . 'hover:[&_tbody_tr]:bg-surface-sunken/40',
    ]) }}>
        {{ $slot }}
    </table>
</div>
