<div class="max-w-3xl">
    <form wire:submit="save" class="space-y-4">
        @foreach ($methods as $method)
            @php
                $key = $method['key'];
                $isOn = (bool) ($enabled[$key] ?? false);
            @endphp
            <x-ui.card>
                {{-- Header row: reorder arrows, name + one-line description, enable toggle.
                     The toggle is live so the config body reveals/hides instantly. --}}
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex items-start gap-3 {{ $isOn ? '' : 'opacity-60' }}">
                        <div class="flex flex-col">
                            <x-ui.icon-button icon="arrow-up" variant="ghost" label="Move up" wire:click="moveUp('{{ $key }}')" @disabled($loop->first) />
                            <x-ui.icon-button icon="arrow-down" variant="ghost" label="Move down" wire:click="moveDown('{{ $key }}')" @disabled($loop->last) />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold text-content">{{ $method['label'] }}</h3>
                                @if ($method['isGateway'])
                                    <x-ui.badge variant="info">Online</x-ui.badge>
                                @endif
                            </div>
                            <p class="mt-0.5 text-sm text-content-muted">{{ $method['description'] }}</p>
                        </div>
                    </div>
                    <x-ui.toggle wire:model.live="enabled.{{ $key }}" label="Enabled" />
                </div>

                {{-- Config body: only rendered while the method is enabled. Methods
                     with no config (e.g. Cash on Delivery) never grow a body. --}}
                @if ($isOn && ($method['isGateway'] || ! empty($method['fields'])))
                    <div class="mt-4 space-y-4 border-t border-line pt-4">
                        @if ($method['isGateway'])
                            <div class="max-w-xs">
                                <x-ui.select wire:model="mode.{{ $key }}" label="Mode">
                                    <option value="test">Test</option>
                                    <option value="live">Live</option>
                                </x-ui.select>
                            </div>
                        @endif

                        @foreach ($method['fields'] as $field)
                            @if (($field['type'] ?? 'text') === 'textarea')
                                <x-ui.textarea
                                    wire:model="config.{{ $key }}.{{ $field['key'] }}"
                                    :label="$field['label']"
                                    :hint="$field['help'] ?? null"
                                    rows="3"
                                />
                            @else
                                <x-ui.input
                                    wire:model="config.{{ $key }}.{{ $field['key'] }}"
                                    :type="($field['secret'] ?? false) ? 'password' : ($field['type'] ?? 'text')"
                                    :label="$field['label']"
                                    :hint="($field['saved'] ?? false) ? 'Saved — leave blank to keep the current value.' : ($field['help'] ?? null)"
                                    :placeholder="($field['saved'] ?? false) ? '••••••••' : null"
                                    autocomplete="off"
                                />
                            @endif
                        @endforeach

                        @if ($method['isGateway'])
                            @php
                                $webhookUrl = route('storefront.payments.webhook', $key);
                                $webhookSteps = [
                                    'Add the URL above as the webhook / notification endpoint in this provider’s dashboard.',
                                    'Fill in any verification secret the provider gives you, then save.',
                                ];
                                $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);
                                $isLocal = in_array($appHost, ['localhost', '127.0.0.1'], true);
                            @endphp

                            {{-- How this gateway tells the store an order is paid. --}}
                            <div class="rounded-md bg-surface-sunken/50 p-3">
                                <p class="text-sm font-medium text-content">Payment confirmation webhook</p>
                                <p class="mt-0.5 text-xs text-content-muted">The provider calls this URL to confirm payments — set it up once when you go live.</p>

                                <div class="mt-2 flex items-center gap-2" x-data>
                                    <input type="text" readonly value="{{ $webhookUrl }}" onclick="this.select()"
                                        class="w-full rounded border border-line bg-surface px-2 py-1.5 font-mono text-xs text-content-secondary"
                                        aria-label="Webhook URL for {{ $method['label'] }}">
                                    {{-- @js() doesn't expand inside an <x-component> tag attribute; use {{ Js::from() }} instead. --}}
                                    <x-ui.icon-button icon="copy" label="Copy webhook URL"
                                        @click="navigator.clipboard?.writeText({{ \Illuminate\Support\Js::from($webhookUrl) }}).then(() => window.toast('Webhook URL copied', 'success'), () => window.toast('Could not copy — click the field and copy manually.', 'warning'))" />
                                </div>

                                <ol class="mt-2 list-decimal space-y-1 pl-5 text-xs text-content-secondary">
                                    @foreach ($webhookSteps as $step)
                                        <li>{{ $step }}</li>
                                    @endforeach
                                </ol>

                                @if ($isLocal)
                                    <p class="mt-2 text-xs font-medium text-warning">
                                        This store runs on {{ $appHost }}, which payment providers can’t reach — webhooks will only
                                        work once the store is on a public domain. Until then, payments confirm when the shopper
                                        returns to the thank-you page.
                                    </p>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif
            </x-ui.card>
        @endforeach

        <x-admin.form-actions>
            <x-ui.save-button target="save" label="Save payment methods" />
        </x-admin.form-actions>
    </form>
</div>
