<?php

/*
|--------------------------------------------------------------------------
| Notification event catalog
|--------------------------------------------------------------------------
| Small, editable starter templates. Admin changes are stored in Settings,
| so package updates do not overwrite the store owner's wording.
*/

return [
    'events' => [
        'order.placed' => [
            'label' => 'Order placed',
            'recipients' => ['customer', 'owner'],
            'channels' => ['email'],
            'variables' => ['customer.name', 'order.number', 'order.total', 'track.url'],
            'templates' => ['email' => [
                'subject' => 'Order {{ order.number }} received',
                'body' => '<p>Hi {{ customer.name }},</p><p>Thank you for your order <strong>{{ order.number }}</strong> totaling {{ order.total }}.</p><p><a href="{{ track.url }}">View your order</a></p>',
            ]],
        ],
        'order.confirmed' => [
            'label' => 'Order confirmed',
            'recipients' => ['customer'],
            'channels' => ['email'],
            'variables' => ['customer.name', 'order.number', 'track.url'],
            'templates' => ['email' => [
                'subject' => 'Your order {{ order.number }} is confirmed',
                'body' => '<p>Hi {{ customer.name }},</p><p>Your order <strong>{{ order.number }}</strong> has been confirmed.</p><p><a href="{{ track.url }}">Track your order</a></p>',
            ]],
        ],
        'order.shipped' => [
            'label' => 'Order shipped',
            'recipients' => ['customer'],
            'channels' => ['email'],
            'variables' => ['customer.name', 'order.number', 'order.tracking_number', 'order.carrier', 'track.url'],
            'templates' => ['email' => [
                'subject' => 'Your order {{ order.number }} has shipped',
                'body' => '<p>Hi {{ customer.name }},</p><p>Your order <strong>{{ order.number }}</strong> is on its way.</p><p><a href="{{ track.url }}">Track your order</a></p>',
            ]],
        ],
        'order.delivered' => [
            'label' => 'Order delivered',
            'recipients' => ['customer'],
            'channels' => ['email'],
            'variables' => ['customer.name', 'order.number'],
            'templates' => ['email' => [
                'subject' => 'Order {{ order.number }} delivered',
                'body' => '<p>Hi {{ customer.name }},</p><p>Your order <strong>{{ order.number }}</strong> has been delivered.</p>',
            ]],
        ],
        'order.cancelled' => [
            'label' => 'Order cancelled',
            'recipients' => ['customer', 'owner'],
            'channels' => ['email'],
            'variables' => ['customer.name', 'order.number', 'order.total'],
            'templates' => ['email' => [
                'subject' => 'Order {{ order.number }} cancelled',
                'body' => '<p>Order <strong>{{ order.number }}</strong> has been cancelled.</p>',
            ]],
        ],
        'order.digital-ready' => [
            'label' => 'Digital order ready',
            'recipients' => ['customer'],
            'channels' => ['email'],
            'variables' => ['customer.name', 'order.number', 'downloads.url'],
            'templates' => ['email' => [
                'subject' => 'Your downloads are ready',
                'body' => '<p>Hi {{ customer.name }},</p><p>Your digital order <strong>{{ order.number }}</strong> is ready.</p><p><a href="{{ downloads.url }}">Download your files</a></p>',
            ]],
        ],
        'customer.welcome' => [
            'label' => 'Customer welcome',
            'recipients' => ['customer'],
            'channels' => ['email'],
            'variables' => ['customer.name', 'store.name', 'store.url'],
            'templates' => ['email' => [
                'subject' => 'Welcome to {{ store.name }}',
                'body' => '<p>Hi {{ customer.name }},</p><p>Welcome to {{ store.name }}.</p><p><a href="{{ store.url }}">Visit the store</a></p>',
            ]],
        ],
    ],
];
