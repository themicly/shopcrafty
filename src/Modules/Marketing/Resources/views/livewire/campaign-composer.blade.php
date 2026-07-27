<div class="grid gap-6 lg:grid-cols-[1.3fr_1fr]">
    <x-ui.card title="New campaign" subtitle="{{ $this->recipientCount }} recipient(s) via your email gateway.">
        <form wire:submit="send" class="space-y-4">
            <div class="space-y-2">
                <p class="text-sm font-medium text-content-secondary">Send to</p>
                <div class="flex items-center gap-1 rounded-md border border-line p-0.5" role="radiogroup">
                    <button type="button" wire:click="$set('audience', 'subscribers')" @class([
                        'flex-1 rounded px-3 py-1.5 text-sm',
                        'bg-surface-sunken font-medium text-content' => $audience === 'subscribers',
                        'text-content-muted hover:text-content' => $audience !== 'subscribers',
                    ])>All subscribers ({{ $subscribedCount }})</button>
                    <button type="button" wire:click="$set('audience', 'customers')" @class([
                        'flex-1 rounded px-3 py-1.5 text-sm',
                        'bg-surface-sunken font-medium text-content' => $audience === 'customers',
                        'text-content-muted hover:text-content' => $audience !== 'customers',
                    ])>Customers by tag</button>
                </div>

                @if ($audience === 'customers')
                    @if (empty($availableTags))
                        <p class="text-xs text-content-muted">No customers are tagged yet — add tags from a customer's profile first.</p>
                    @else
                        <x-ui.multiselect
                            wire-model="tags"
                            :options="collect($availableTags)->map(fn ($t) => ['value' => $t, 'label' => $t])->all()"
                            :value="$tags"
                            placeholder="Pick one or more tags…"
                            :hint="$this->recipientCount.' matching customer(s) with an email on file.'"
                        />
                        @error('tags') <p class="text-xs text-danger">{{ $message }}</p> @enderror
                    @endif
                @endif
            </div>

            @if ($this->aiEnabled)
                <div class="space-y-3 rounded-lg border border-line bg-surface p-3">
                    <div class="flex items-end gap-2">
                        <div class="min-w-0 flex-1">
                            <x-ui.input wire:model="aiGoal" label="What's this campaign about?"
                                placeholder="e.g. Eid sale, 20% off electronics, ends Friday"
                                wire:keydown.enter.prevent="generateWithAi" />
                        </div>
                        <x-admin.ai-button action="generateWithAi" label="Generate with AI" size="md" class="h-9" />
                    </div>
                </div>
            @endif

            <x-ui.input wire:model="subject" label="Subject" :error="$errors->first('subject')" />
            <x-ui.textarea wire:model="body" label="Body (HTML allowed)" rows="10" :error="$errors->first('body')"
                :hint="$audience === 'subscribers' ? 'An unsubscribe link is appended automatically.' : 'Sent to a customer segment, not the subscriber list — no unsubscribe link is appended.'" />
            <x-ui.save-button type="button" target="send" label="Send campaign" loadingLabel="Queuing…"
                x-on:click="$dispatch('confirm', { title: 'Queue campaign?', message: 'This queues the campaign to {{ $this->recipientCount }} recipient(s).', confirmLabel: 'Send campaign', variant: 'primary', onConfirm: () => $wire.send() })" />
        </form>
    </x-ui.card>

    <x-ui.card title="Recent campaigns">
        @if ($campaigns->isEmpty())
            <x-ui.empty-state icon="marketing" title="No campaigns yet" description="Your sent broadcasts will be listed here." />
        @else
            <div class="divide-y divide-line">
                @foreach ($campaigns as $campaign)
                    <div class="py-2.5">
                        <p class="truncate text-sm font-medium text-content">{{ $campaign->subject }}</p>
                        <p class="text-xs text-content-muted">
                            {{ $campaign->recipients_count }} sent
                            @if ($campaign->audience_tags)
                                · tagged {{ implode(', ', $campaign->audience_tags) }}
                            @else
                                · all subscribers
                            @endif
                            · {{ $campaign->sent_at?->diffForHumans() ?? 'draft' }}
                        </p>
                    </div>
                @endforeach
            </div>
        @endif
    </x-ui.card>

    @if (app(\Themicly\Shopcrafty\Core\Module\AddonRegistry::class)->installed('ai') && isset($aiReview))
        <x-admin.ai-review-modal :items="$aiReview" />
    @endif
</div>
