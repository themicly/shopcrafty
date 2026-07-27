{{--
    Pre-install fallback: the themed error page extends theme::layout, which
    calls settings() throughout (header, footer, nav) — none of that has a
    database to read from yet on a fresh, unconfigured upload. This is
    deliberately self-contained (no settings(), no theme, no DB) so a stray
    request (a browser/crawler probing an unmapped path, say) can never
    double-fault while the wizard hasn't run yet.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $code ?? 'Error' }} — {{ config('app.name', 'Shopcrafty') }}</title>
</head>
<body style="margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; background:#fafafa; color:#18181b; font:16px/1.5 system-ui, -apple-system, sans-serif;">
    <div style="text-align:center; padding:2rem; max-width:28rem;">
        <p style="font-size:3rem; font-weight:700; margin:0; color:#71717a;">{{ $code ?? '' }}</p>
        <h1 style="font-size:1.375rem; font-weight:600; margin:0.5rem 0;">{{ $title ?? 'Something went wrong' }}</h1>
        <p style="margin:0 0 1.5rem; color:#71717a;">{{ $message ?? 'An unexpected error occurred.' }}</p>
        <a href="{{ url('/install') }}" style="color:#18181b; font-weight:600;">Go to the installer &rarr;</a>
    </div>
</body>
</html>
