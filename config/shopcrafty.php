<?php

return [
    'store_name' => env('SHOPCRAFTY_STORE_NAME', env('APP_NAME', 'Shopcrafty')),
    'auth_rate_limit' => (int) env('SHOPCRAFTY_AUTH_RATE_LIMIT', 10),
    'admin_email' => env('SHOPCRAFTY_ADMIN_EMAIL', 'admin@example.com'),
    'admin_password' => env('SHOPCRAFTY_ADMIN_PASSWORD', 'password'),
    // Set this to a host application directory to install custom themes.
    // When null, the bundled package themes are used automatically.
    'themes_path' => null,
    'available_locales' => [
        'en' => 'English', 'es' => 'Español', 'fr' => 'Français',
        'pt' => 'Português', 'de' => 'Deutsch', 'ar' => 'العربية',
    ],
];
