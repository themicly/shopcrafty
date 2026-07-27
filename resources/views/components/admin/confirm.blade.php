{{--
    Global admin confirmation bridge.

    A single branded modal that replaces native browser `wire:confirm` prompts
    across the admin. Trigger it from any button with an Alpine `confirm` event
    that carries the copy plus an `onConfirm` callback to run when accepted:

        <button type="button" x-on:click="$dispatch('confirm', {
            title: 'Delete product?',
            message: 'This permanently removes the product.',
            confirmLabel: 'Delete',
            variant: 'danger',           // 'danger' (default) or 'primary'
            onConfirm: () => $wire.delete({{ $id }}),
        })">Delete</button>

    Because the callback is passed by reference in the event detail it keeps the
    dispatching component's `$wire` scope, so the exact same Livewire method /
    arguments run — only the confirmation UX changes.
--}}
<div
    x-data="{
        show: false,
        title: 'Are you sure?',
        message: null,
        confirmLabel: 'Confirm',
        variant: 'danger',
        onConfirm: null,
        open(detail) {
            detail = detail || {};
            this.title = detail.title || 'Are you sure?';
            this.message = detail.message || null;
            this.confirmLabel = detail.confirmLabel || 'Confirm';
            this.variant = detail.variant || 'danger';
            this.onConfirm = typeof detail.onConfirm === 'function' ? detail.onConfirm : null;
            this.show = true;
            this.$nextTick(() => this.$refs.confirmBtn && this.$refs.confirmBtn.focus());
        },
        run() {
            const cb = this.onConfirm;
            this.show = false;
            this.onConfirm = null;
            if (cb) cb();
        },
    }"
    @confirm.window="open($event.detail)"
    @keydown.escape.window="show = false"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-[60] overflow-y-auto"
    style="display: none;"
>
    <div x-show="show" x-transition.opacity class="fixed inset-0 bg-black/50" @click="show = false"></div>

    <div class="flex min-h-full items-start justify-center p-4 sm:p-6">
        <div
            x-show="show"
            x-transition
            @click.outside="show = false"
            role="alertdialog"
            aria-modal="true"
            class="relative mt-[8vh] w-full max-w-sm rounded-xl border border-line bg-surface-overlay shadow-lg"
        >
            <div class="flex items-center justify-between gap-4 border-b border-line px-5 py-4">
                <h3 class="text-sm font-semibold text-content" x-text="title"></h3>
                <button type="button" @click="show = false" class="grid h-8 w-8 place-items-center rounded-md text-content-muted hover:bg-surface-sunken" aria-label="Close">
                    <span class="text-lg leading-none">&times;</span>
                </button>
            </div>

            <div class="p-5" x-show="message">
                <p class="text-sm text-content-secondary" x-text="message"></p>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-line px-5 py-4">
                <x-ui.button variant="ghost" x-on:click="show = false">Cancel</x-ui.button>
                <x-ui.button variant="danger" x-ref="confirmBtn" x-show="variant === 'danger'" x-on:click="run()" x-text="confirmLabel"></x-ui.button>
                <x-ui.button variant="primary" x-show="variant !== 'danger'" x-on:click="run()" x-text="confirmLabel"></x-ui.button>
            </div>
        </div>
    </div>
</div>
