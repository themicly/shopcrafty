<?php

/*
|--------------------------------------------------------------------------
| Checkout & order-tracking strings (English)
|--------------------------------------------------------------------------
| Shopper-facing copy for the checkout flow, thank-you page and order
| tracker rendered by the default theme. Reference with __('checkout.<key>').
| Generic shared words (Subtotal, Total, Shipping, …) live in storefront.php.
*/

return [

    'checkout' => 'Checkout',

    // --- Thank you ---
    'order_confirmed_title' => 'Order confirmed',
    'your_order' => 'Your order',
    'order_placed_message' => "has been placed. We'll be in touch shortly.",
    'payment_label' => 'Payment:',
    'ship_to' => 'Ship to:',

    // --- Order tracking ---
    'track_your_order' => 'Track your order',
    'track_subtitle' => 'Enter your order number and phone to see its status.',
    'order_number_placeholder' => 'Order number (e.g. BZ-01001)',
    'track' => 'Track',
    'no_order_found' => 'No order found with that number and phone.',

    // --- Express / wallets ---
    'express_checkout' => 'Express checkout',
    'express_pay_with' => 'Express — pay with :method',
    'starting' => 'Starting…',
    'pay_with' => 'Pay with :label',
    'or_pay_another_way' => 'or pay another way',

    // --- Contact ---
    'contact' => 'Contact',
    'phone_number' => 'Phone number',
    'email_optional' => 'Email (optional)',
    'email_for_delivery' => 'Email (for delivery)',

    // --- Shipping ---
    'use_saved_address' => 'Use a saved address',
    'saved_address' => 'Saved address',
    'enter_new_address' => 'Enter a new address…',
    'street_address' => 'Street address',
    'select_placeholder' => 'Select :label…',
    'state_region' => 'State / Region',
    'shipping_method' => 'Shipping method',
    'select_delivery_area' => 'Select your delivery area above to see the shipping rate.',
    'delivery' => 'Delivery',
    'digital_delivery_note' => 'Your items are digital — download links will be available right after checkout and emailed to you. No shipping required.',

    // --- Payment ---
    'badge_card' => 'Card',
    'badge_online' => 'Online',
    'badge_offline' => 'Offline',
    'loading_payment_form' => 'Loading secure payment form…',
    'complete_payment_on' => "You'll complete payment on :method.",
    'redirect_note' => "After you place your order we'll send you to :method to pay securely, then bring you back.",
    'order_notes_optional' => 'Order notes (optional)',

    // --- Payment method names / instructions ---
    'payment_cod' => 'Cash on Delivery',
    'payment_bank_transfer' => 'Bank Transfer',
    'payment_stripe' => 'Card (Stripe)',
    'cod_instructions' => 'Pay in cash when your order is delivered.',
    'bank_transfer_instructions_default' => 'Transfer to our bank account and email the receipt.',

    // --- Summary ---
    'qty_label' => 'Qty:',
    'coupon_word' => 'Coupon',
    'applied_word' => 'applied',
    'includes' => 'Includes',
    'placing_order' => 'Placing order…',
    'secure_encrypted_checkout' => 'Secure, encrypted checkout',

    // --- Errors / feedback ---
    'express_unavailable' => 'That express payment option is unavailable.',
    'check_highlighted_fields' => 'Please check the highlighted fields.',
    'too_many_attempts' => 'Too many attempts. Please wait a moment and try again.',
    'select_full_delivery_area' => 'Please select your full delivery area.',
    'insufficient_stock' => 'Not enough stock for :product.',
    'payment_start_failed' => "Payment couldn't start: :reason",
    'payment_start_failed_generic' => "Payment couldn't start.",
    'order_saved_unpaid' => 'Your order is saved as unpaid — you can retry or pick another method.',
    'payment_method_unavailable' => 'That payment method is not available.',
    'cart_is_empty' => 'Your cart is empty.',
    'order_placement_disabled_demo' => 'Order placement is disabled in this demo.',
];
