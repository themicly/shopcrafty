<?php

return [
    'store_name' => env('SHOPCRAFTY_STORE_NAME', env('APP_NAME', 'Shopcrafty')),
    'auth_rate_limit' => (int) env('SHOPCRAFTY_AUTH_RATE_LIMIT', 10),
    'admin_email' => env('SHOPCRAFTY_ADMIN_EMAIL', 'admin@example.com'),
    'admin_password' => env('SHOPCRAFTY_ADMIN_PASSWORD', 'password'),
    // Set this to a host application directory to install custom themes.
    // When null, the bundled package themes are used automatically.
    'themes_path' => null,
    // Vendor themes are bundled as internal fallbacks and are hidden from the
    // selectable theme catalog unless explicitly enabled by the host app.
    'include_vendor_themes' => false,
    'available_locales' => [
        'en' => 'English', 'es' => 'Español', 'fr' => 'Français',
        'pt' => 'Português', 'de' => 'Deutsch', 'ar' => 'العربية',
    ],
];
