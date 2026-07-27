<?php

return [
    'main' => [
        ['label' => 'Overview', 'items' => [
            ['label' => 'Dashboard', 'icon' => 'dashboard', 'route' => 'admin.dashboard'],
            ['label' => 'Reports', 'icon' => 'reports', 'route' => 'admin.reports.index', 'gate' => 'manage-config'],
        ]],
        ['label' => 'Commerce', 'items' => [
            ['label' => 'Products', 'icon' => 'products', 'route' => 'admin.catalog.products.index', 'gate' => 'manage-products'],
            ['label' => 'Inventory', 'icon' => 'reports', 'route' => 'admin.catalog.inventory.index', 'gate' => 'manage-products'],
            ['label' => 'Orders', 'icon' => 'orders', 'route' => 'admin.orders.index', 'gate' => 'manage-orders'],
            ['label' => 'Customers', 'icon' => 'customers', 'route' => 'admin.customers.index', 'gate' => 'manage-customers'],
        ]],
        ['label' => 'Grow', 'items' => [
            ['label' => 'Coupons', 'icon' => 'marketing', 'route' => 'admin.marketing.coupons.index', 'gate' => 'manage-marketing'],
        ]],
        ['label' => 'Store setup', 'items' => [
            ['label' => 'Website', 'icon' => 'themes', 'route' => 'admin.themes.index', 'gate' => 'manage-content'],
            ['label' => 'Menus', 'icon' => 'content', 'route' => 'admin.cms.menus.index', 'gate' => 'manage-content'],
            ['label' => 'Pages', 'icon' => 'content', 'route' => 'admin.cms.pages.index', 'gate' => 'manage-content'],
            ['label' => 'Media', 'icon' => 'image', 'route' => 'admin.media.index', 'gate' => 'manage-content'],
        ]],
    ],
    'footer' => [
        ['label' => 'Settings', 'icon' => 'settings', 'route' => 'admin.settings.index', 'gate' => 'manage-config'],
        ['label' => 'Logs', 'icon' => 'reports', 'route' => 'admin.notifications.logs', 'gate' => 'manage-config'],
    ],
    'quick_create' => [
        ['label' => 'New Product', 'route' => 'admin.catalog.products.create', 'gate' => 'manage-products'],
        ['label' => 'New Order', 'route' => 'admin.orders.create', 'gate' => 'manage-orders'],
        ['label' => 'New Coupon', 'route' => 'admin.marketing.coupons.create', 'gate' => 'manage-marketing'],
        ['label' => 'New Customer', 'route' => 'admin.customers.create', 'gate' => 'manage-customers'],
    ],
];
