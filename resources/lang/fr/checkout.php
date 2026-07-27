<?php

/*
|--------------------------------------------------------------------------
| Checkout & order-tracking strings (Français)
|--------------------------------------------------------------------------
| Shopper-facing copy for the checkout flow, thank-you page and order
| tracker rendered by the default theme. Reference with __('checkout.<key>').
| Generic shared words (Subtotal, Total, Shipping, …) live in storefront.php.
*/

return [

    'checkout' => 'Paiement',

    // --- Thank you ---
    'order_confirmed_title' => 'Commande confirmée',
    'your_order' => 'Votre commande',
    'order_placed_message' => 'a été passée. Nous vous contacterons prochainement.',
    'payment_label' => 'Paiement :',
    'ship_to' => 'Livrer à :',

    // --- Order tracking ---
    'track_your_order' => 'Suivez votre commande',
    'track_subtitle' => 'Saisissez votre numéro de commande et votre téléphone pour voir son statut.',
    'order_number_placeholder' => 'Numéro de commande (ex. BZ-01001)',
    'track' => 'Suivre',
    'no_order_found' => 'Aucune commande trouvée avec ce numéro et ce téléphone.',

    // --- Express / wallets ---
    'express_checkout' => 'Paiement express',
    'express_pay_with' => 'Express — payer avec :method',
    'starting' => 'Démarrage…',
    'pay_with' => 'Payer avec :label',
    'or_pay_another_way' => 'ou payer autrement',

    // --- Contact ---
    'contact' => 'Contact',
    'phone_number' => 'Numéro de téléphone',
    'email_optional' => 'E-mail (facultatif)',
    'email_for_delivery' => 'E-mail (pour la livraison)',

    // --- Shipping ---
    'use_saved_address' => 'Utiliser une adresse enregistrée',
    'saved_address' => 'Adresse enregistrée',
    'enter_new_address' => 'Saisir une nouvelle adresse…',
    'street_address' => 'Adresse',
    'select_placeholder' => 'Sélectionnez :label…',
    'state_region' => 'État / Région',
    'shipping_method' => 'Mode de livraison',
    'select_delivery_area' => 'Sélectionnez votre zone de livraison ci-dessus pour voir le tarif d’expédition.',
    'delivery' => 'Livraison',
    'digital_delivery_note' => 'Vos articles sont numériques — les liens de téléchargement seront disponibles juste après le paiement et vous seront envoyés par e-mail. Aucune livraison requise.',

    // --- Payment ---
    'badge_card' => 'Carte',
    'badge_online' => 'En ligne',
    'badge_offline' => 'Hors ligne',
    'loading_payment_form' => 'Chargement du formulaire de paiement sécurisé…',
    'complete_payment_on' => 'Vous finaliserez le paiement sur :method.',
    'redirect_note' => 'Après avoir passé votre commande, nous vous enverrons vers :method pour payer en toute sécurité, puis nous vous ramènerons ici.',
    'order_notes_optional' => 'Remarques sur la commande (facultatif)',

    // --- Payment method names / instructions ---
    'payment_cod' => 'Paiement à la livraison',
    'payment_bank_transfer' => 'Virement bancaire',
    'payment_stripe' => 'Carte (Stripe)',
    'cod_instructions' => 'Payez en espèces à la livraison de votre commande.',
    'bank_transfer_instructions_default' => 'Effectuez un virement vers notre compte bancaire et envoyez-nous le reçu par e-mail.',

    // --- Summary ---
    'qty_label' => 'Qté :',
    'coupon_word' => 'Code promo',
    'applied_word' => 'appliqué',
    'includes' => 'Inclut',
    'placing_order' => 'Validation de la commande…',
    'secure_encrypted_checkout' => 'Paiement sécurisé et chiffré',

    // --- Errors / feedback ---
    'express_unavailable' => "Cette option de paiement express n'est pas disponible.",
    'check_highlighted_fields' => 'Veuillez vérifier les champs surlignés.',
    'too_many_attempts' => 'Trop de tentatives. Veuillez patienter un instant et réessayer.',
    'select_full_delivery_area' => 'Veuillez sélectionner votre zone de livraison complète.',
    'insufficient_stock' => 'Stock insuffisant pour :product.',
    'payment_start_failed' => "Le paiement n'a pas pu démarrer : :reason",
    'payment_start_failed_generic' => "Le paiement n'a pas pu démarrer.",
    'order_saved_unpaid' => 'Votre commande est enregistrée comme non payée — vous pouvez réessayer ou choisir une autre méthode.',
    'payment_method_unavailable' => "Ce mode de paiement n'est pas disponible.",
    'cart_is_empty' => 'Votre panier est vide.',
    'order_placement_disabled_demo' => 'La validation de commande est désactivée dans cette démo.',
];
