<?php

/*
|--------------------------------------------------------------------------
| Checkout & order-tracking strings (العربية)
|--------------------------------------------------------------------------
| Shopper-facing copy for the checkout flow, thank-you page and order
| tracker rendered by the default theme. Reference with __('checkout.<key>').
| Generic shared words (Subtotal, Total, Shipping, …) live in storefront.php.
*/

return [

    'checkout' => 'إتمام الشراء',

    // --- Thank you ---
    'order_confirmed_title' => 'تم تأكيد الطلب',
    'your_order' => 'طلبك',
    'order_placed_message' => 'تم إرساله. سنتواصل معك قريباً.',
    'payment_label' => 'الدفع:',
    'ship_to' => 'الشحن إلى:',

    // --- Order tracking ---
    'track_your_order' => 'تتبع طلبك',
    'track_subtitle' => 'أدخل رقم طلبك وهاتفك لمعرفة حالته.',
    'order_number_placeholder' => 'رقم الطلب (مثال: BZ-01001)',
    'track' => 'تتبع',
    'no_order_found' => 'لم يتم العثور على طلب بهذا الرقم والهاتف.',

    // --- Express / wallets ---
    'express_checkout' => 'الدفع السريع',
    'express_pay_with' => 'سريع — الدفع عبر :method',
    'starting' => 'جارٍ البدء…',
    'pay_with' => 'الدفع عبر :label',
    'or_pay_another_way' => 'أو ادفع بطريقة أخرى',

    // --- Contact ---
    'contact' => 'التواصل',
    'phone_number' => 'رقم الهاتف',
    'email_optional' => 'البريد الإلكتروني (اختياري)',
    'email_for_delivery' => 'البريد الإلكتروني (للتوصيل)',

    // --- Shipping ---
    'use_saved_address' => 'استخدام عنوان محفوظ',
    'saved_address' => 'عنوان محفوظ',
    'enter_new_address' => 'إدخال عنوان جديد…',
    'street_address' => 'عنوان الشارع',
    'select_placeholder' => 'اختر :label…',
    'state_region' => 'المحافظة / المنطقة',
    'shipping_method' => 'طريقة الشحن',
    'select_delivery_area' => 'اختر منطقة التوصيل أعلاه لمعرفة تكلفة الشحن.',
    'delivery' => 'التوصيل',
    'digital_delivery_note' => 'منتجاتك رقمية — ستتوفر روابط التنزيل مباشرة بعد إتمام الشراء وسترسل إليك عبر البريد الإلكتروني. لا حاجة للشحن.',

    // --- Payment ---
    'badge_card' => 'بطاقة',
    'badge_online' => 'عبر الإنترنت',
    'badge_offline' => 'غير متصل',
    'loading_payment_form' => 'جارٍ تحميل نموذج الدفع الآمن…',
    'complete_payment_on' => 'ستكمل الدفع عبر :method.',
    'redirect_note' => 'بعد إتمام طلبك، سننقلك إلى :method للدفع بأمان، ثم نعيدك إلى هنا.',
    'order_notes_optional' => 'ملاحظات الطلب (اختياري)',

    // --- Payment method names / instructions ---
    'payment_cod' => 'الدفع عند الاستلام',
    'payment_bank_transfer' => 'تحويل بنكي',
    'payment_stripe' => 'بطاقة (Stripe)',
    'cod_instructions' => 'ادفع نقدًا عند استلام طلبك.',
    'bank_transfer_instructions_default' => 'حوّل المبلغ إلى حسابنا البنكي وأرسل لنا إيصال التحويل عبر البريد الإلكتروني.',

    // --- Summary ---
    'qty_label' => 'الكمية:',
    'coupon_word' => 'الكوبون',
    'applied_word' => 'مُطبَّق',
    'includes' => 'يتضمن',
    'placing_order' => 'جارٍ إتمام الطلب…',
    'secure_encrypted_checkout' => 'دفع آمن ومشفّر',

    // --- Errors / feedback ---
    'express_unavailable' => 'خيار الدفع السريع هذا غير متاح.',
    'check_highlighted_fields' => 'يرجى مراجعة الحقول المظللة.',
    'too_many_attempts' => 'محاولات كثيرة جدًا. يرجى الانتظار قليلاً والمحاولة مرة أخرى.',
    'select_full_delivery_area' => 'يرجى تحديد منطقة التوصيل بالكامل.',
    'insufficient_stock' => 'الكمية المتوفرة غير كافية لـ :product.',
    'payment_start_failed' => 'تعذّر بدء الدفع: :reason',
    'payment_start_failed_generic' => 'تعذّر بدء الدفع.',
    'order_saved_unpaid' => 'تم حفظ طلبك كغير مدفوع — يمكنك إعادة المحاولة أو اختيار طريقة أخرى.',
    'payment_method_unavailable' => 'طريقة الدفع هذه غير متاحة.',
    'cart_is_empty' => 'سلتك فارغة.',
    'order_placement_disabled_demo' => 'إتمام الطلبات معطّل في هذا العرض التجريبي.',
];
