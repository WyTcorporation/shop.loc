<?php

return [
    'admin_email' => env('SHOP_ADMIN_EMAIL'), // опціонально
    'product_images_disk' => env('SHOP_PRODUCT_IMAGES_DISK', 'public'),
    'loyalty' => [
        'earn_rate' => (float) env('SHOP_LOYALTY_EARN_RATE', 1),
        'redeem_value' => (float) env('SHOP_LOYALTY_REDEEM_VALUE', 0.1),
        'max_redeem_percent' => (float) env('SHOP_LOYALTY_MAX_REDEEM_PERCENT', 0.5),
    ],
    'currency' => [
        'base' => strtoupper((string) env('SHOP_CURRENCY_BASE', 'EUR')),
        'provider' => env('SHOP_CURRENCY_PROVIDER', 'https://open.er-api.com/v6/latest/{base}'),
        'timeout' => (int) env('SHOP_CURRENCY_PROVIDER_TIMEOUT', 15),
    ],
];
