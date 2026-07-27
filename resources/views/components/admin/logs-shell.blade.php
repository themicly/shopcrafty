@php
    // Every log/audit trail in the admin lives together here, split out of
    // Settings so the Settings nav doesn't have to carry read-only history
    // alongside configuration screens.
    $nav = [
        ['label' => 'Logs', 'items' => [
            ['route' => 'admin.notifications.logs', 'label' => 'Delivery log', 'desc' => 'Every send attempt & outcome', 'gate' => 'manage-config'],
            ['route' => 'admin.orders.payment-log', 'label' => 'Payment log', 'desc' => 'Gateway sessions, webhooks & errors', 'gate' => 'manage-orders'],
            ['route' => 'admin.settings.audit', 'label' => 'Activity log', 'desc' => 'Recent config changes', 'gate' => 'manage-config'],
        ]],
    ];
@endphp

<x-admin.section-shell title="Logs" subtitle="Delivery, payment and configuration history." :nav="$nav">
    {{ $slot }}
</x-admin.section-shell>
