<?php

return [
    'admin_email' => env('SHOP_ADMIN_EMAIL'), // опціонально
    'loyalty' => [
        'earn_rate' => (float) env('SHOP_LOYALTY_EARN_RATE', 1),
        'redeem_value' => (float) env('SHOP_LOYALTY_REDEEM_VALUE', 0.1),
        'max_redeem_percent' => (float) env('SHOP_LOYALTY_MAX_REDEEM_PERCENT', 0.5),
    ],
];
