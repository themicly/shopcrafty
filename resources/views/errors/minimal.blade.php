{{--
    Dispatches to a themed (storefront) or plain (DB-free) error page. Any
    request can hit this — including ones that never pass through
    EnsureInstalled at all, like a truly unmatched URL (no route ⇒ Laravel's
    router 404s before any middleware runs) — so this check, not that
    middleware, is what actually keeps a stray pre-install request from
    trying to render a settings()-dependent theme with no database yet.

    installed() only checks the lock file, not that the database is actually
    reachable (wrong/blank credentials — e.g. an overwritten .env — DB server
    down, etc.). Picking the themed branch on the lock file alone previously
    meant a DB outage crashed this very fallback page: settings() failed,
    Laravel tried to render an error page for *that*, which needed
    settings() again, recursing until "headers already sent". Ping the
    connection first so a DB outage always degrades to the plain page.
--}}
@php
    $canUseThemed = false;
    if (\Themicly\Shopcrafty\Modules\Settings\Services\InstallerService::installed()) {
        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            $canUseThemed = true;
        } catch (\Throwable $e) {
            $canUseThemed = false;
        }
    }
@endphp

@if ($canUseThemed)
    @include('errors._minimal-themed')
@else
    @include('errors._minimal-plain')
@endif
