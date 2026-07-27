<?php

/*
|--------------------------------------------------------------------------
| Checkout & order-tracking strings (Deutsch)
|--------------------------------------------------------------------------
| Shopper-facing copy for the checkout flow, thank-you page and order
| tracker rendered by the default theme. Reference with __('checkout.<key>').
| Generic shared words (Subtotal, Total, Shipping, …) live in storefront.php.
*/

return [

    'checkout' => 'Kasse',

    // --- Thank you ---
    'order_confirmed_title' => 'Bestellung bestätigt',
    'your_order' => 'Deine Bestellung',
    'order_placed_message' => 'wurde aufgegeben. Wir melden uns in Kürze bei dir.',
    'payment_label' => 'Zahlung:',
    'ship_to' => 'Lieferung an:',

    // --- Order tracking ---
    'track_your_order' => 'Verfolge deine Bestellung',
    'track_subtitle' => 'Gib deine Bestellnummer und Telefonnummer ein, um den Status zu sehen.',
    'order_number_placeholder' => 'Bestellnummer (z. B. BZ-01001)',
    'track' => 'Verfolgen',
    'no_order_found' => 'Keine Bestellung mit dieser Nummer und Telefonnummer gefunden.',

    // --- Express / wallets ---
    'express_checkout' => 'Express-Checkout',
    'express_pay_with' => 'Express — bezahlen mit :method',
    'starting' => 'Wird gestartet…',
    'pay_with' => 'Bezahlen mit :label',
    'or_pay_another_way' => 'oder anders bezahlen',

    // --- Contact ---
    'contact' => 'Kontakt',
    'phone_number' => 'Telefonnummer',
    'email_optional' => 'E-Mail (optional)',
    'email_for_delivery' => 'E-Mail (für die Lieferung)',

    // --- Shipping ---
    'use_saved_address' => 'Gespeicherte Adresse verwenden',
    'saved_address' => 'Gespeicherte Adresse',
    'enter_new_address' => 'Neue Adresse eingeben…',
    'street_address' => 'Straße und Hausnummer',
    'select_placeholder' => ':label auswählen…',
    'state_region' => 'Bundesland / Region',
    'shipping_method' => 'Versandart',
    'select_delivery_area' => 'Wähle oben dein Liefergebiet aus, um die Versandkosten zu sehen.',
    'delivery' => 'Lieferung',
    'digital_delivery_note' => 'Deine Artikel sind digital — Download-Links stehen direkt nach dem Bezahlen zur Verfügung und werden dir per E-Mail zugeschickt. Kein Versand erforderlich.',

    // --- Payment ---
    'badge_card' => 'Karte',
    'badge_online' => 'Online',
    'badge_offline' => 'Offline',
    'loading_payment_form' => 'Sicheres Zahlungsformular wird geladen…',
    'complete_payment_on' => 'Du schließt die Zahlung auf :method ab.',
    'redirect_note' => 'Nach der Bestellung schicken wir dich zu :method, um sicher zu bezahlen, und bringen dich anschließend zurück.',
    'order_notes_optional' => 'Anmerkungen zur Bestellung (optional)',

    // --- Payment method names / instructions ---
    'payment_cod' => 'Nachnahme',
    'payment_bank_transfer' => 'Banküberweisung',
    'payment_stripe' => 'Karte (Stripe)',
    'cod_instructions' => 'Bezahle bar bei Lieferung deiner Bestellung.',
    'bank_transfer_instructions_default' => 'Überweise auf unser Bankkonto und schicke uns den Beleg per E-Mail.',

    // --- Summary ---
    'qty_label' => 'Menge:',
    'coupon_word' => 'Gutschein',
    'applied_word' => 'angewendet',
    'includes' => 'Enthält',
    'placing_order' => 'Bestellung wird aufgegeben…',
    'secure_encrypted_checkout' => 'Sicherer, verschlüsselter Checkout',

    // --- Errors / feedback ---
    'express_unavailable' => 'Diese Express-Zahlungsoption ist nicht verfügbar.',
    'check_highlighted_fields' => 'Bitte überprüfe die markierten Felder.',
    'too_many_attempts' => 'Zu viele Versuche. Bitte warte einen Moment und versuche es erneut.',
    'select_full_delivery_area' => 'Bitte wähle dein vollständiges Liefergebiet aus.',
    'insufficient_stock' => 'Nicht genügend Bestand für :product.',
    'payment_start_failed' => 'Die Zahlung konnte nicht gestartet werden: :reason',
    'payment_start_failed_generic' => 'Die Zahlung konnte nicht gestartet werden.',
    'order_saved_unpaid' => 'Deine Bestellung ist als unbezahlt gespeichert — du kannst es erneut versuchen oder eine andere Methode wählen.',
    'payment_method_unavailable' => 'Diese Zahlungsmethode ist nicht verfügbar.',
    'cart_is_empty' => 'Dein Warenkorb ist leer.',
    'order_placement_disabled_demo' => 'Das Aufgeben von Bestellungen ist in dieser Demo deaktiviert.',
];
