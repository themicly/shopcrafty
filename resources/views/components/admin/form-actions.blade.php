{{-- Sticky bottom bar for a settings form's save/submit action(s) — stays
     reachable without scrolling to the end of a long form. Uses position:sticky
     (not fixed) so it naturally inherits the form's own width and never needs
     to account for the sidebar's collapsed/expanded/off-canvas width. --}}
<div class="sticky bottom-0 z-10 mt-8 flex items-center gap-3 border-t border-line bg-surface/95 py-4 backdrop-blur">
    {{ $slot }}
</div>
