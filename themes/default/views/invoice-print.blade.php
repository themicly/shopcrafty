@php $invStoreName = settings('general.store_name', config('app.name')); @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('storefront.invoice') }} {{ $order->number }} · {{ $invStoreName }}</title>
    @if ($favicon = settings('general.favicon'))<link rel="icon" href="{{ $favicon }}">@endif
    <style>
        /* Self-contained, ink-on-white so the invoice reads the same for every
           theme and prints cleanly. No app CSS needed. */
        :root { --st-ink: #111; --st-ink-soft: #555; --st-line: #ccc; }
        * { box-sizing: border-box; }
        body {
            margin: 0; background: #f3f3f1; color: var(--st-ink);
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        .inv-toolbar {
            position: sticky; top: 0; z-index: 10;
            display: flex; align-items: center; justify-content: space-between; gap: 12px;
            padding: 12px 16px; background: #fff; border-bottom: 1px solid var(--st-line);
        }
        .inv-toolbar .inv-title { font-size: 13px; font-weight: 600; color: var(--st-ink-soft); }
        .inv-toolbar .inv-actions { display: flex; gap: 8px; }
        .inv-btn {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 16px; font-size: 13px; font-weight: 600; cursor: pointer;
            border: 1px solid var(--st-line); border-radius: 8px; background: #fff; color: var(--st-ink);
            text-decoration: none;
        }
        .inv-btn--primary { background: #111; border-color: #111; color: #fff; }

        .inv-sheet {
            max-width: 760px; margin: 24px auto; padding: 40px;
            background: #fff; border: 1px solid var(--st-line); border-radius: 12px;
        }

        /* Invoice document styles (shared with the former print partial). */
        .st-invoice { font-size: 13px; line-height: 1.5; color: var(--st-ink); }
        .st-invoice a { color: inherit; text-decoration: none; }
        .st-inv-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; padding-bottom: 16px; border-bottom: 2px solid var(--st-ink); }
        .st-inv-store { font-size: 20px; font-weight: 700; letter-spacing: -.01em; }
        .st-inv-soft { color: var(--st-ink-soft); }
        .st-inv-title { font-size: 15px; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; text-align: right; }
        .st-inv-meta { margin-top: 16px; display: flex; justify-content: space-between; gap: 24px; flex-wrap: wrap; }
        .st-inv-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--st-ink-soft); margin-bottom: 3px; }
        .st-inv-table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        .st-inv-table th { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--st-ink-soft); text-align: left; padding: 6px 8px; border-bottom: 1px solid var(--st-ink); }
        .st-inv-table td { padding: 7px 8px; border-bottom: 1px solid var(--st-line); vertical-align: top; }
        .st-inv-num { text-align: right; white-space: nowrap; }
        .st-inv-totals { margin-top: 12px; margin-left: auto; width: 260px; max-width: 100%; }
        .st-inv-totals .st-inv-row { display: flex; justify-content: space-between; padding: 3px 8px; }
        .st-inv-totals .st-inv-grand { border-top: 2px solid var(--st-ink); margin-top: 4px; padding-top: 7px; font-size: 15px; font-weight: 700; }
        .st-inv-foot { margin-top: 28px; padding-top: 12px; border-top: 1px solid var(--st-line); text-align: center; color: var(--st-ink-soft); font-size: 12px; }

        @media (max-width: 480px) { .inv-sheet { margin: 12px; padding: 24px; } }

        @media print {
            @page { margin: 14mm; }
            body { background: #fff; }
            .inv-toolbar { display: none !important; }
            .inv-sheet { margin: 0; padding: 0; border: 0; max-width: none; }
        }
    </style>
</head>
<body>
    <div class="inv-toolbar">
        <span class="inv-title">{{ __('storefront.invoice') }} · {{ $order->number }}</span>
        <div class="inv-actions">
            <a href="{{ route('storefront.thankyou', $order->number) }}" class="inv-btn">{{ __('storefront.back') }}</a>
            <button type="button" class="inv-btn inv-btn--primary" onclick="window.print()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="16" height="16" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659"/></svg>
                {{ __('storefront.print_download_invoice') }}
            </button>
        </div>
    </div>

    <div class="inv-sheet">
        @include('theme::partials.invoice-document', ['order' => $order])
    </div>
</body>
</html>
