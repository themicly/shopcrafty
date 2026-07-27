<x-layouts.admin title="Pages">
    <x-admin.page-header title="Pages" subtitle="About, contact, policies and landing pages.">
        <x-slot:actions>
            <x-ui.button :href="route('admin.cms.pages.create')">New page</x-ui.button>
        </x-slot:actions>
    </x-admin.page-header>
    <livewire:cms.page-list />
</x-layouts.admin>
