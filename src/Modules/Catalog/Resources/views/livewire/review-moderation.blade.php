<div class="space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-1 rounded-md border border-line p-0.5">
            @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'all' => 'All'] as $value => $label)
                <button wire:click="$set('status', '{{ $value }}')" @class([
                    'rounded px-3 py-1.5 text-sm',
                    'bg-surface-sunken font-medium text-content' => $status === $value,
                    'text-content-muted hover:text-content' => $status !== $value,
                ])>
                    {{ $label }}
                    @if ($value === 'pending' && $pendingCount > 0)
                        <span class="ml-1 rounded-full bg-warning-soft px-1.5 text-xs text-warning">{{ $pendingCount }}</span>
                    @endif
                </button>
            @endforeach
        </div>
        <x-ui.toggle wire:model.live="autoApprove" label="Auto-approve new reviews" />
    </div>

    @if ($digestProductId && $digestText !== '')
        <div class="rounded-lg border border-primary/30 bg-primary-soft px-4 py-3">
            <div class="flex items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="flex items-center gap-1.5 text-sm font-semibold text-content">
                        <x-ui.icon name="sparkles" class="h-4 w-4 text-primary" />
                        AI review digest — {{ $digestProductName }}
                    </p>
                    <p class="mt-1.5 text-sm text-content-secondary">{{ $digestText }}</p>
                    <p class="mt-1.5 text-xs text-content-muted">Summarized from recent written reviews. Regenerates when new reviews come in.</p>
                </div>
                <button type="button" wire:click="dismissDigest" class="shrink-0 text-content-muted hover:text-content" aria-label="Dismiss digest">
                    <span class="text-lg leading-none">&times;</span>
                </button>
            </div>
        </div>
    @endif

    @if ($reviews->isEmpty())
        <x-ui.card><x-ui.empty-state icon="products" title="No reviews here" /></x-ui.card>
    @else
        <div class="space-y-3">
            @foreach ($reviews as $review)
                <x-ui.card>
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-sm" style="color: #f59e0b">{{ str_repeat('★', $review->rating).str_repeat('☆', 5 - $review->rating) }}</span>
                                <x-ui.badge :variant="match ($review->status) { 'approved' => 'success', 'rejected' => 'danger', default => 'warning' }">{{ ucfirst($review->status) }}</x-ui.badge>
                                @if ($review->verified_purchase)<x-ui.badge variant="primary">Verified</x-ui.badge>@endif
                            </div>
                            <p class="mt-2 text-sm text-content-muted">
                                on <a href="{{ $review->product ? route('admin.catalog.products.edit', $review->product_id) : '#' }}" class="font-medium text-content hover:text-primary">{{ $review->product?->name ?? '—' }}</a>
                                · {{ $review->author_name }} · {{ $review->created_at?->diffForHumans() }}
                            </p>
                            @if ($review->title)<p class="mt-2 text-sm font-semibold text-content">{{ $review->title }}</p>@endif
                            <p class="mt-1 text-sm text-content-secondary">{{ $review->body }}</p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            @if ($this->digestEnabled && $review->product_id && in_array($review->product_id, $digestableProductIds))
                                <x-admin.ai-button action="generateDigest({{ $review->product_id }})" label="AI digest" />
                            @endif
                            @if ($review->status !== 'approved')
                                <x-ui.button size="sm" variant="primary" wire:click="approve({{ $review->id }})">Approve</x-ui.button>
                            @endif
                            @if ($review->status !== 'rejected')
                                <x-ui.button size="sm" variant="ghost" wire:click="reject({{ $review->id }})">Reject</x-ui.button>
                            @endif
                            <x-ui.icon-button icon="trash" variant="danger" label="Delete" type="button" x-on:click="$dispatch('confirm', { title: 'Delete review?', message: 'This permanently deletes the review.', confirmLabel: 'Delete', onConfirm: () => $wire.delete({{ $review->id }}) })" />
                        </div>
                    </div>
                </x-ui.card>
            @endforeach
        </div>
        <div>{{ $reviews->links() }}</div>
    @endif
</div>
