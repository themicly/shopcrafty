<div class="space-y-4">
    @php
        $channelMeta = [
            'email' => ['label' => 'Email', 'icon' => 'bell'],
            'sms' => ['label' => 'SMS', 'icon' => 'bell'],
        ];
    @endphp

    {{-- Event list --}}
    <div class="space-y-3">
        @foreach ($this->events as $event)
            <x-ui.card>
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <h3 class="font-semibold text-content">{{ $event['label'] }}</h3>
                            @if (empty($event['enabled']))
                                <x-ui.badge variant="neutral">Off</x-ui.badge>
                            @endif
                        </div>
                        <p class="mt-1 font-mono text-xs text-content-muted">{{ $event['key'] }}</p>
                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                            <span class="text-xs text-content-muted">Sends to:</span>
                            @foreach ($event['recipients'] as $recipient)
                                <x-ui.badge variant="info">{{ ucfirst($recipient) }}</x-ui.badge>
                            @endforeach
                        </div>
                    </div>

                    {{-- Channel toggles --}}
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach ($event['available'] as $channel)
                            @php $on = in_array($channel, $event['enabled'], true); @endphp
                            <button
                                type="button"
                                wire:click="toggleChannel('{{ $event['key'] }}', '{{ $channel }}')"
                                @class([
                                    'inline-flex items-center gap-1.5 rounded-full border px-3 py-1.5 text-xs font-medium transition-colors',
                                    'border-primary bg-primary-soft text-primary' => $on,
                                    'border-line text-content-muted hover:border-content-muted' => ! $on,
                                ])
                            >
                                <span @class(['h-1.5 w-1.5 rounded-full', 'bg-primary' => $on, 'bg-line' => ! $on])></span>
                                {{ $channelMeta[$channel]['label'] ?? ucfirst($channel) }}
                            </button>
                        @endforeach

                        <x-ui.button variant="ghost" size="sm" wire:click="edit('{{ $event['key'] }}')">
                            Edit templates
                        </x-ui.button>
                    </div>
                </div>

                {{-- Inline template editor --}}
                @if ($editing === $event['key'])
                    <div class="mt-5 space-y-5 rounded-lg border border-primary/30 bg-primary-soft/30 p-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-sm font-semibold text-content">Message templates</h4>
                            <button type="button" wire:click="cancelEdit" class="text-sm text-content-muted hover:text-content">Close</button>
                        </div>

                        {{-- Variable helper --}}
                        @if (! empty($event['variables']))
                            <div class="rounded-md border border-line bg-surface-raised p-3">
                                <p class="mb-2 text-xs font-medium text-content-secondary">Available variables — copy into any field:</p>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach ($event['variables'] as $var)
                                        <code class="rounded bg-surface-sunken px-1.5 py-0.5 font-mono text-xs text-content-secondary">&#123;&#123; {{ $var }} &#125;&#125;</code>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @foreach ($templates as $channel => $tpl)
                            <div class="space-y-3 rounded-md border border-line bg-surface-raised p-3">
                                <div class="flex items-center gap-2">
                                    <x-ui.badge variant="primary">{{ $channelMeta[$channel]['label'] ?? ucfirst($channel) }}</x-ui.badge>
                                </div>
                                @if (array_key_exists('subject', $tpl) && $tpl['subject'] !== null)
                                    <x-ui.input wire:model="templates.{{ $channel }}.subject" label="Subject" />
                                @endif
                                <x-ui.textarea wire:model="templates.{{ $channel }}.body" label="Body" :rows="$channel === 'email' ? 6 : 3" />
                            </div>
                        @endforeach

                        <div class="flex items-center gap-3">
                            <x-ui.save-button type="button" target="saveTemplates" label="Save templates" wire:click="saveTemplates" />
                            <x-ui.button variant="ghost" x-on:click="$dispatch('confirm', { title: 'Reset templates?', message: 'This resets these templates to the built-in defaults and discards your changes.', confirmLabel: 'Reset', onConfirm: () => $wire.resetTemplates() })">
                                Reset to default
                            </x-ui.button>
                        </div>
                    </div>
                @endif
            </x-ui.card>
        @endforeach
    </div>
</div>
