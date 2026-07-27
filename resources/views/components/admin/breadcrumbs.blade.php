@props(['title' => null])

@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    // Derive a trail from the route name: admin.catalog.products.index → Catalog › Products
    $name = str_replace('admin.', '', request()->route()?->getName() ?? '');
    $segments = collect(explode('.', $name))
        ->reject(fn ($s) => $s === '' || in_array($s, ['index', 'show', 'edit', 'create'], true))
        ->values();

    // Build each crumb with a best-effort link to its own section index route
    // (admin.<segments…>.index), so ancestors are navigable and the current
    // (last) crumb stays inert text.
    $crumbs = $segments->map(function ($segment, $i) use ($segments) {
        $candidate = 'admin.'.$segments->slice(0, $i + 1)->implode('.').'.index';

        return [
            'label' => Str::headline(str_replace('-', ' ', $segment)),
            'href' => Route::has($candidate) ? route($candidate) : null,
        ];
    });
@endphp

{{-- Below sm, only the current page's own crumb shows — the ancestor trail is
     hidden per-item (not the whole nav) so a page title is always visible in
     the topbar, not just on wider screens. --}}
<nav class="flex min-w-0 items-center gap-1.5 text-sm" aria-label="Breadcrumb">
    @forelse ($crumbs as $crumb)
        @unless ($loop->first)<span class="hidden text-content-muted sm:inline">/</span>@endunless
        @if (! $loop->last && $crumb['href'])
            <a href="{{ $crumb['href'] }}" class="hidden text-content-muted transition-colors hover:text-content sm:inline">{{ $crumb['label'] }}</a>
        @else
            <span class="{{ $loop->last ? 'truncate font-semibold text-content' : 'hidden text-content-muted sm:inline' }}" @if ($loop->last) aria-current="page" @endif>{{ $crumb['label'] }}</span>
        @endif
    @empty
        @if ($title)<span class="font-semibold text-content">{{ $title }}</span>@endif
    @endforelse
</nav>
