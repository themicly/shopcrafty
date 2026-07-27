<x-layouts.admin title="Notification gateways">
    <x-admin.settings-shell>
        <livewire:notifications.notification-gateways :channel="$channel" :key="'gw-'.$channel" />
    </x-admin.settings-shell>
</x-layouts.admin>
