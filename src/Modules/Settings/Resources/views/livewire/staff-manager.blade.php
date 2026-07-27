<div class="max-w-3xl space-y-4">
    <div class="flex justify-end">
        <x-ui.button size="sm" wire:click="$toggle('showForm')">{{ $showForm ? 'Close' : 'Add member' }}</x-ui.button>
    </div>

    @if ($showForm)
        <x-ui.card title="Add team member" subtitle="They'll set their own password with “forgot password”.">
            <form wire:submit="create" class="space-y-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-ui.input wire:model="name" label="Name" :error="$errors->first('name')" />
                    <x-ui.input wire:model="email" type="email" label="Email" :error="$errors->first('email')" />
                </div>
                <x-ui.select wire:model.live="role" label="Role" class="max-w-xs">
                    <option value="staff">Staff</option>
                    <option value="owner">Owner</option>
                </x-ui.select>
                <p class="text-xs text-content-muted">Owners can change store settings, payments and this team list. Staff run day-to-day operations.</p>

                @if ($role === 'staff')
                    <div>
                        <p class="mb-2 text-sm font-medium text-content-secondary">Permissions</p>
                        <div class="grid gap-2 sm:grid-cols-2">
                            @foreach ($permissionOptions as $key => $label)
                                <label class="flex items-center gap-2 text-sm text-content-secondary">
                                    <input type="checkbox" wire:model="permissions" value="{{ $key }}" class="rounded border-line text-primary focus:ring-primary">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <x-ui.button type="submit">Add member</x-ui.button>
            </form>
        </x-ui.card>
    @endif

    <x-ui.card title="Team">
        <div class="divide-y divide-line">
            @foreach ($members as $member)
                <div class="py-3" wire:key="staff-{{ $member->id }}">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="truncate font-medium text-content">{{ $member->name }}</span>
                                <x-ui.badge :variant="$member->role === 'owner' ? 'primary' : 'neutral'">{{ ucfirst($member->role) }}</x-ui.badge>
                                @unless ($member->status === 'active')<x-ui.badge variant="warning">Suspended</x-ui.badge>@endunless
                            </div>
                            <p class="truncate text-xs text-content-muted">{{ $member->email }}</p>
                            @if ($member->role === 'staff')
                                <p class="mt-0.5 text-xs text-content-muted">
                                    {{ $member->permissions ? 'Access: '.count($member->permissions).' area(s)' : 'No areas granted yet' }}
                                </p>
                            @endif
                        </div>
                        <div class="flex shrink-0 items-center justify-end gap-1">
                            @if ($member->role === 'staff')
                                <x-ui.icon-button icon="pencil" variant="ghost" label="Permissions" wire:click="editPermissions({{ $member->id }})" />
                            @endif
                            <x-ui.button size="sm" variant="ghost" wire:click="toggleStatus({{ $member->id }})">{{ $member->status === 'active' ? 'Suspend' : 'Restore' }}</x-ui.button>
                            <x-ui.icon-button icon="trash" variant="danger" label="Remove" wire:click="remove({{ $member->id }})" wire:confirm="Remove this team member?" />
                        </div>
                    </div>

                    {{-- Inline permission editor --}}
                    @if ($editingId === $member->id)
                        <div class="mt-3 rounded-lg border border-line bg-surface-sunken p-3">
                            <div class="grid gap-2 sm:grid-cols-2">
                                @foreach ($permissionOptions as $key => $label)
                                    <label class="flex items-center gap-2 text-sm text-content-secondary">
                                        <input type="checkbox" wire:model="editPermissions" value="{{ $key }}" class="rounded border-line text-primary focus:ring-primary">
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                            <div class="mt-3 flex justify-end gap-2">
                                <x-ui.button size="sm" variant="ghost" wire:click="$set('editingId', null)">Cancel</x-ui.button>
                                <x-ui.save-button type="button" target="savePermissions" label="Save permissions" size="sm" wire:click="savePermissions" />
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </x-ui.card>
</div>
