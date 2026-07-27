{{--
    "Print / download invoice" button — shared fallback partial, rendered under
    every theme. Usage: @include('theme::partials.invoice', ['order' => $order])

    Opens the standalone printable invoice page (theme::invoice-print) in a new
    tab. That page shows the invoice on screen (a real preview) with its own
    Print / Save-as-PDF button — far more reliable than the previous hidden-clone
    + window.print() trick, which never showed a preview.
--}}
<a href="{{ route('storefront.invoice', $order->number) }}" target="_blank" rel="noopener"
    class="inline-flex items-center gap-2 border px-6 py-3 text-sm font-semibold"
    style="border-color: var(--st-line); background: var(--st-bg); color: var(--st-ink); border-radius: var(--st-radius-sm)"
    aria-label="{{ __('storefront.print_invoice_aria', ['number' => $order->number]) }}">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" class="h-4 w-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659"/></svg>
    {{ __('storefront.print_download_invoice') }}
</a>
