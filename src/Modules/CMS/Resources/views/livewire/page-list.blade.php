<div>
    <div class="mb-4 flex items-center justify-end">
        <x-ui.button :href="route('admin.cms.pages.create')"><x-ui.icon name="plus" class="h-4 w-4" /> New page</x-ui.button>
    </div>

    <x-admin.list-toolbar :count="$pages->total()">
        <div class="w-64">
            <x-ui.input wire:model.live.debounce.400ms="search" placeholder="Search pages…" aria-label="Search pages" />
        </div>
        <x-ui.select wire:model.live="statusFilter" class="w-40" aria-label="Filter by status">
            <option value="">All statuses</option>
            <option value="draft">Draft</option>
            <option value="published">Published</option>
        </x-ui.select>
    </x-admin.list-toolbar>

    @if ($pages->isEmpty())
        <div class="rounded-lg border border-line bg-surface-raised">
            @if ($search !== '' || $statusFilter !== '')
                <x-ui.empty-state icon="search" title="No pages match" description="Try a different search or clear the filters.">
                    <x-slot:action>
                        <x-ui.button size="sm" variant="secondary" wire:click="$set('search', ''); $set('statusFilter', '')">Clear filters</x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            @else
                <x-ui.empty-state icon="content" title="No pages yet" description="Create your first page or landing page.">
                    <x-slot:action>
                        <x-ui.button :href="route('admin.cms.pages.create')" size="sm">New page</x-ui.button>
                    </x-slot:action>
                </x-ui.empty-state>
            @endif
        </div>
    @else
        <x-ui.table>
            <thead><tr><th>Title</th><th>Type</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
                @foreach ($pages as $page)
                    <tr wire:key="page-{{ $page->id }}">
                        <td class="font-medium text-content"><a href="{{ route('admin.cms.pages.edit', $page) }}" class="hover:text-primary hover:underline focus-visible:underline focus-visible:outline-none">{{ $page->title }}</a></td>
                        <td class="capitalize text-content-secondary">{{ $page->type }}</td>
                        <td>@if ($page->status === 'published')<x-ui.badge variant="success">Published</x-ui.badge>@else<x-ui.badge variant="warning">Draft</x-ui.badge>@endif</td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1">
                                @if ($page->status === 'published')
                                    <x-ui.icon-button icon="eye" variant="ghost" label="View on storefront" :href="route('storefront.page', $page->slug)" target="_blank" rel="noopener" />
                                @endif
                                <x-ui.icon-button icon="pencil" variant="ghost" label="Edit" :href="route('admin.cms.pages.edit', $page)" />
                                <x-ui.icon-button icon="trash" variant="danger" label="Delete" x-on:click="$dispatch('confirm', { title: 'Delete page?', message: 'This permanently deletes the page.', confirmLabel: 'Delete', variant: 'danger', onConfirm: () => $wire.delete({{ $page->id }}) })" />
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </x-ui.table>
        <div class="mt-4">{{ $pages->links() }}</div>
    @endif
</div>
