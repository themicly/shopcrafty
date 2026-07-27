{{-- Storefront toast viewport. The global toast store lives in app.js (fed by
     window.toast(...) and Livewire dispatch('toast', ...)); this renders it.
     Included once per layout (main + checkout) so toasts are never silent.
     Styled with --st-* tokens so it matches whichever theme is active. --}}
<div x-data aria-live="polite" class="pointer-events-none fixed end-4 top-4 z-[60] flex w-full max-w-[calc(100vw-2rem)] flex-col gap-2 sm:max-w-sm">
    <template x-for="t in $store.toasts.items" :key="t.id">
        <div
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 ltr:translate-x-4 rtl:-translate-x-4"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="pointer-events-auto flex items-start gap-3 border px-4 py-3 shadow-lg"
            {{-- A plain style="" attribute alongside :style is NOT merged by Alpine —
                 x-bind:style replaces the whole inline style attribute on every
                 update (see Alpine's setStylesFromString), so the base background/
                 border/radius has to live inside the single reactive binding too,
                 or every non-default-bordered toast renders with no background at all. --}}
            :style="`background: var(--st-surface); border-radius: var(--st-radius-sm); border-color: ${(t.type === 'danger' || t.type === 'error') ? 'var(--st-accent)' : 'var(--st-line)'}`"
        >
            <div class="min-w-0 flex-1">
                <p x-show="t.title" x-text="t.title" class="text-sm font-semibold" style="color: var(--st-ink)"></p>
                <p x-text="t.message" class="text-sm" style="color: var(--st-ink)"></p>
            </div>
            <button type="button" @click="$store.toasts.remove(t.id)" class="text-lg leading-none" style="color: var(--st-ink-soft)" aria-label="{{ __('storefront.dismiss') }}">&times;</button>
        </div>
    </template>
</div>
