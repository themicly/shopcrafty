<?php

/*
|--------------------------------------------------------------------------
| Checkout & order-tracking strings (Português)
|--------------------------------------------------------------------------
| Shopper-facing copy for the checkout flow, thank-you page and order
| tracker rendered by the default theme. Reference with __('checkout.<key>').
| Generic shared words (Subtotal, Total, Shipping, …) live in storefront.php.
*/

return [

    'checkout' => 'Finalizar compra',

    // --- Thank you ---
    'order_confirmed_title' => 'Pedido confirmado',
    'your_order' => 'Seu pedido',
    'order_placed_message' => 'foi realizado. Entraremos em contato em breve.',
    'payment_label' => 'Pagamento:',
    'ship_to' => 'Enviar para:',

    // --- Order tracking ---
    'track_your_order' => 'Rastreie seu pedido',
    'track_subtitle' => 'Digite o número do pedido e o telefone para ver o status.',
    'order_number_placeholder' => 'Número do pedido (ex. BZ-01001)',
    'track' => 'Rastrear',
    'no_order_found' => 'Nenhum pedido encontrado com esse número e telefone.',

    // --- Express / wallets ---
    'express_checkout' => 'Checkout expresso',
    'express_pay_with' => 'Expresso — pagar com :method',
    'starting' => 'Iniciando…',
    'pay_with' => 'Pagar com :label',
    'or_pay_another_way' => 'ou pague de outra forma',

    // --- Contact ---
    'contact' => 'Contato',
    'phone_number' => 'Número de telefone',
    'email_optional' => 'E-mail (opcional)',
    'email_for_delivery' => 'E-mail (para entrega)',

    // --- Shipping ---
    'use_saved_address' => 'Usar um endereço salvo',
    'saved_address' => 'Endereço salvo',
    'enter_new_address' => 'Digitar um novo endereço…',
    'street_address' => 'Endereço',
    'select_placeholder' => 'Selecione :label…',
    'state_region' => 'Estado / Região',
    'shipping_method' => 'Modo de envio',
    'select_delivery_area' => 'Selecione sua área de entrega acima para ver o valor do frete.',
    'delivery' => 'Entrega',
    'digital_delivery_note' => 'Seus itens são digitais — os links de download estarão disponíveis logo após a compra e serão enviados por e-mail. Sem necessidade de frete.',

    // --- Payment ---
    'badge_card' => 'Cartão',
    'badge_online' => 'On-line',
    'badge_offline' => 'Offline',
    'loading_payment_form' => 'Carregando formulário de pagamento seguro…',
    'complete_payment_on' => 'Você concluirá o pagamento em :method.',
    'redirect_note' => 'Após fazer o pedido, vamos te enviar para :method para pagar com segurança e depois te trazer de volta.',
    'order_notes_optional' => 'Observações do pedido (opcional)',

    // --- Payment method names / instructions ---
    'payment_cod' => 'Pagamento na entrega',
    'payment_bank_transfer' => 'Transferência bancária',
    'payment_stripe' => 'Cartão (Stripe)',
    'cod_instructions' => 'Pague em dinheiro na entrega do seu pedido.',
    'bank_transfer_instructions_default' => 'Transfira para nossa conta bancária e envie o comprovante por e-mail.',

    // --- Summary ---
    'qty_label' => 'Qtd:',
    'coupon_word' => 'Cupom',
    'applied_word' => 'aplicado',
    'includes' => 'Inclui',
    'placing_order' => 'Finalizando pedido…',
    'secure_encrypted_checkout' => 'Checkout seguro e criptografado',

    // --- Errors / feedback ---
    'express_unavailable' => 'Essa opção de pagamento expresso não está disponível.',
    'check_highlighted_fields' => 'Por favor, verifique os campos destacados.',
    'too_many_attempts' => 'Muitas tentativas. Aguarde um instante e tente novamente.',
    'select_full_delivery_area' => 'Por favor, selecione sua área de entrega completa.',
    'insufficient_stock' => 'Estoque insuficiente para :product.',
    'payment_start_failed' => 'Não foi possível iniciar o pagamento: :reason',
    'payment_start_failed_generic' => 'Não foi possível iniciar o pagamento.',
    'order_saved_unpaid' => 'Seu pedido foi salvo como não pago — você pode tentar novamente ou escolher outro método.',
    'payment_method_unavailable' => 'Esse método de pagamento não está disponível.',
    'cart_is_empty' => 'Seu carrinho está vazio.',
    'order_placement_disabled_demo' => 'Fazer pedidos está desativado nesta demonstração.',
];
