<div class="max-w-3xl space-y-6">
    <form wire:submit="save" class="space-y-6">
        @foreach ($this->sections as $section)
            @php $channel = $section['channel']; @endphp
            <section class="space-y-4" wire:key="channel-{{ $channel }}">
                <div>
                    <h2 class="text-sm font-semibold text-content">{{ $section['label'] }} provider</h2>
                    <p class="mt-0.5 text-sm text-content-muted">
                        Turn on the provider that should deliver {{ $section['label'] }} messages — only one is active at a time.
                    </p>
                </div>

                @unless ($section['enabled'])
                    <div class="rounded-lg border border-warning/30 bg-warning-soft px-3 py-2.5 text-sm text-content-secondary">
                        {{ $section['label'] }} sending is off — no {{ $section['label'] }} messages will go out until you turn a provider on.
                    </div>
                @endunless

                {{-- One card per provider: header + toggle; enabling reveals config + test send. --}}
                @foreach ($section['gateways'] as $gateway)
                    <x-ui.card wire:key="card-{{ $channel }}-{{ $gateway['key'] }}">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-semibold text-content">{{ $gateway['label'] }}</h3>
                                    @if ($gateway['configured'])
                                        <x-ui.badge variant="success">Configured</x-ui.badge>
                                    @elseif ($gateway['on'])
                                        <x-ui.badge variant="warning">Needs setup</x-ui.badge>
                                    @endif
                                </div>
                                <p class="mt-0.5 text-sm text-content-muted">{{ $gateway['description'] }}</p>
                            </div>
                            {{-- Radio-like: turning one on switches the others off; turning the
                                 active one off disables the channel. Click is prevented so the
                                 server round-trip decides the final state. --}}
                            <x-ui.toggle
                                :checked="$gateway['on']"
                                wire:click.prevent="toggleProvider('{{ $channel }}', '{{ $gateway['key'] }}')"
                                :label="$gateway['on'] ? 'On' : 'Off'"
                            />
                        </div>

                        @if ($gateway['on'])
                            <div class="mt-4 space-y-4 border-t border-line pt-4">
                                @if ($section['guide'])
                                    <div class="rounded-lg border border-info/30 bg-info-soft px-3 py-2.5 text-sm text-content-secondary">
                                        {{ $section['guide']['text'] }}
                                        @if ($section['guide']['url'])
                                            <a href="{{ $section['guide']['url'] }}" target="_blank" rel="noopener" class="ml-1 font-medium text-info hover:underline">
                                                {{ $section['guide']['label'] }} ↗
                                            </a>
                                        @endif
                                    </div>
                                @endif

                                @if (! empty($section['fields']))
                                    <div class="grid gap-4 sm:grid-cols-2">
                                        @foreach ($section['fields'] as $field)
                                            @if (($field['type'] ?? 'text') === 'select')
                                                <x-ui.select
                                                    wire:model="config.{{ $channel }}.{{ $field['key'] }}"
                                                    :label="$field['label']"
                                                    :hint="$field['help'] ?? null"
                                                >
                                                    @foreach ($field['options'] ?? [] as $value => $optionLabel)
                                                        <option value="{{ $value }}">{{ $optionLabel }}</option>
                                                    @endforeach
                                                </x-ui.select>
                                            @else
                                                <x-ui.input
                                                    wire:model="config.{{ $channel }}.{{ $field['key'] }}"
                                                    :type="($field['secret'] ?? false) ? 'password' : ($field['type'] ?? 'text')"
                                                    :label="$field['label']"
                                                    :hint="($field['saved'] ?? false) ? 'Saved — leave blank to keep the current value.' : ($field['help'] ?? null)"
                                                    :placeholder="($field['saved'] ?? false) ? '••••••••' : null"
                                                    autocomplete="off"
                                                />
                                            @endif
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-content-muted">This provider needs no extra configuration.</p>
                                @endif

                                {{-- Test send --}}
                                <div class="space-y-1.5 border-t border-line pt-4">
                                    <div class="flex flex-wrap items-end gap-3">
                                        <div class="w-full max-w-xs">
                                            <x-ui.input
                                                wire:model="testTo.{{ $channel }}"
                                                :label="'Send a test '.$channel"
                                                :placeholder="$channel === 'email' ? 'you@example.com' : '+8801XXXXXXXXX'"
                                            />
                                        </div>
                                        <x-ui.button type="button" variant="secondary" wire:click="sendTest('{{ $channel }}')">
                                            <span wire:loading.remove wire:target="sendTest('{{ $channel }}')">Save &amp; send test</span>
                                            <span wire:loading wire:target="sendTest('{{ $channel }}')">Sending…</span>
                                        </x-ui.button>
                                    </div>
                                    <p class="text-xs text-content-muted">Testing saves this page first, then sends through {{ $gateway['label'] }}.</p>
                                </div>
                            </div>
                        @endif
                    </x-ui.card>
                @endforeach
            </section>
        @endforeach

        <div class="flex items-center gap-3">
            <x-ui.save-button target="save" label="Save gateways" />
        </div>
    </form>
</div>
