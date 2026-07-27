<?php

/*
|--------------------------------------------------------------------------
| Checkout & order-tracking strings (Español)
|--------------------------------------------------------------------------
| Shopper-facing copy for the checkout flow, thank-you page and order
| tracker rendered by the default theme. Reference with __('checkout.<key>').
| Generic shared words (Subtotal, Total, Shipping, …) live in storefront.php.
*/

return [

    'checkout' => 'Pago',

    // --- Thank you ---
    'order_confirmed_title' => 'Pedido confirmado',
    'your_order' => 'Tu pedido',
    'order_placed_message' => 'ha sido realizado. Nos pondremos en contacto en breve.',
    'payment_label' => 'Pago:',
    'ship_to' => 'Enviar a:',

    // --- Order tracking ---
    'track_your_order' => 'Rastrea tu pedido',
    'track_subtitle' => 'Introduce tu número de pedido y teléfono para ver su estado.',
    'order_number_placeholder' => 'Número de pedido (p. ej. BZ-01001)',
    'track' => 'Rastrear',
    'no_order_found' => 'No se encontró ningún pedido con ese número y teléfono.',

    // --- Express / wallets ---
    'express_checkout' => 'Pago exprés',
    'express_pay_with' => 'Exprés — pagar con :method',
    'starting' => 'Iniciando…',
    'pay_with' => 'Pagar con :label',
    'or_pay_another_way' => 'o paga de otra forma',

    // --- Contact ---
    'contact' => 'Contacto',
    'phone_number' => 'Número de teléfono',
    'email_optional' => 'Correo electrónico (opcional)',
    'email_for_delivery' => 'Correo electrónico (para la entrega)',

    // --- Shipping ---
    'use_saved_address' => 'Usar una dirección guardada',
    'saved_address' => 'Dirección guardada',
    'enter_new_address' => 'Introducir una nueva dirección…',
    'street_address' => 'Dirección',
    'select_placeholder' => 'Selecciona :label…',
    'state_region' => 'Estado / Región',
    'shipping_method' => 'Método de envío',
    'select_delivery_area' => 'Selecciona tu zona de entrega arriba para ver la tarifa de envío.',
    'delivery' => 'Entrega',
    'digital_delivery_note' => 'Tus artículos son digitales — los enlaces de descarga estarán disponibles justo después del pago y se te enviarán por correo. No se requiere envío.',

    // --- Payment ---
    'badge_card' => 'Tarjeta',
    'badge_online' => 'En línea',
    'badge_offline' => 'Sin conexión',
    'loading_payment_form' => 'Cargando formulario de pago seguro…',
    'complete_payment_on' => 'Completarás el pago en :method.',
    'redirect_note' => 'Después de realizar tu pedido, te enviaremos a :method para pagar de forma segura y luego te traeremos de vuelta.',
    'order_notes_optional' => 'Notas del pedido (opcional)',

    // --- Payment method names / instructions ---
    'payment_cod' => 'Pago contra entrega',
    'payment_bank_transfer' => 'Transferencia bancaria',
    'payment_stripe' => 'Tarjeta (Stripe)',
    'cod_instructions' => 'Paga en efectivo cuando recibas tu pedido.',
    'bank_transfer_instructions_default' => 'Transfiere a nuestra cuenta bancaria y envíanos el comprobante por correo.',

    // --- Summary ---
    'qty_label' => 'Cant.:',
    'coupon_word' => 'Cupón',
    'applied_word' => 'aplicado',
    'includes' => 'Incluye',
    'placing_order' => 'Realizando pedido…',
    'secure_encrypted_checkout' => 'Pago seguro y cifrado',

    // --- Errors / feedback ---
    'express_unavailable' => 'Esa opción de pago exprés no está disponible.',
    'check_highlighted_fields' => 'Por favor, revisa los campos resaltados.',
    'too_many_attempts' => 'Demasiados intentos. Espera un momento y vuelve a intentarlo.',
    'select_full_delivery_area' => 'Por favor, selecciona tu zona de entrega completa.',
    'insufficient_stock' => 'No hay suficiente stock de :product.',
    'payment_start_failed' => 'No se pudo iniciar el pago: :reason',
    'payment_start_failed_generic' => 'No se pudo iniciar el pago.',
    'order_saved_unpaid' => 'Tu pedido se guardó como no pagado — puedes reintentarlo o elegir otro método.',
    'payment_method_unavailable' => 'Ese método de pago no está disponible.',
    'cart_is_empty' => 'Tu carrito está vacío.',
    'order_placement_disabled_demo' => 'Realizar pedidos está desactivado en esta demo.',
];
