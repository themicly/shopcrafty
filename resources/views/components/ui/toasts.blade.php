{{-- Global toast viewport. Rendered once per layout. --}}
<div class="pointer-events-none fixed right-4 top-4 z-[60] flex w-full max-w-sm flex-col gap-2">
    <template x-for="t in $store.toasts.items" :key="t.id">
        <div
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-x-4"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="pointer-events-auto flex items-start gap-3 rounded-lg border px-4 py-3 shadow-lg"
            :class="{
                'border-success/30 bg-success-soft': t.type === 'success',
                'border-warning/30 bg-warning-soft': t.type === 'warning',
                'border-danger/30 bg-danger-soft': t.type === 'danger',
                'border-line bg-surface-overlay': t.type === 'info',
            }"
        >
            <div class="min-w-0 flex-1">
                <p x-show="t.title" x-text="t.title" class="text-sm font-medium text-content"></p>
                <p x-text="t.message" class="text-sm text-content-secondary"></p>
            </div>
            <button type="button" @click="$store.toasts.remove(t.id)" class="text-content-muted hover:text-content" aria-label="Dismiss">
                <span class="text-lg leading-none">&times;</span>
            </button>
        </div>
    </template>
</div>
