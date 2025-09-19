<?php

return [
    'navigation' => [
        'catalog' => 'Catalog',
        'cart' => 'Cart',
        'order' => 'Order',
    ],

    'conversation' => [
        'heading' => 'Conversation',
        'system' => 'System',
        'empty' => 'No messages yet.',
        'new' => 'New message',
        'send' => 'Send',
        'sent' => 'Message sent',
        'message' => 'Message',
    ],

    'common' => [
        'owner' => 'Owner',
        'email' => 'Email',
        'phone' => 'Phone',
        'footer_note' => 'If you have any questions, simply reply to this email.',
        'order_title' => 'Order #:number',
        'updates_email' => 'We will send updates to :email.',
        'order_number' => 'Order number',
        'items_total' => 'Items total',
        'coupon' => 'Coupon',
        'discount' => 'Discount',
        'used_points' => 'Used points',
        'order_total' => 'Order total',
        'total_due' => 'Total due',
        'amount_due' => 'Amount due',
        'status' => 'Status',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'paid' => 'Paid',
        'shipped_at' => 'Shipment date',
        'delivered_at' => 'Delivery date',
        'paid_at' => 'Payment date',
        'product' => 'Product',
        'quantity' => 'Qty',
        'price' => 'Price',
        'sum' => 'Total',
        'items_subtotal' => 'Items subtotal',
    ],

    'auth' => [
        'greeting' => 'Hello, :name!',
        'reset_link_hint' => 'A reset link will be sent if the account exists.',
        'reset' => [
            'subject' => 'Password reset for :app',
            'heading' => 'Restore access to :app',
            'intro' => 'You are receiving this email because we received a password reset request for your account at :app.',
            'title' => 'Reset password',
            'button' => 'Reset password',
            'link_help' => 'Button not working? Copy and paste this link into your browser:',
            'ignore' => 'If you did not request a password reset, simply ignore this email.',
            'changed_subject' => 'Password for :app changed',
            'changed_title' => 'Password changed',
            'changed_intro' => 'We just updated the password for your account at :app.',
            'changed_warning' => 'If you did not change your password, contact our support or reset it again right away to protect your account.',
            'signature' => 'Best regards, the :app team.',
        ],
        'welcome' => [
            'subject' => 'Welcome to :app!',
            'title' => 'Welcome to :app',
            'intro' => 'Thank you for registering at :app. To finish setting up your account, please confirm your email address.',
            'button' => 'Confirm email address',
            'ignore' => 'If you did not create an account, simply ignore this email.',
        ],
        'verify' => [
            'subject' => 'Confirm email address for :app',
            'title' => 'Confirm email address',
            'intro' => 'To activate your account at :app, please confirm your email address within the next hour.',
            'button' => 'Confirm email address',
            'ignore' => 'If you did not create an account, simply ignore this email.',
        ],
    ],

    'orders' => [
        'placed' => [
            'subject' => 'Thank you for your order!',
            'subject_line' => 'Your order #:number has been received',
            'intro' => 'Order #:number has been placed.',
        ],
        'paid' => [
            'subject' => 'Payment received',
            'subject_line' => 'Order #:number paid',
            'intro' => 'Order #:number has been paid successfully.',
            'next' => 'We are preparing it for shipment and will let you know about the next steps.',
            'button' => 'Back to shop',
        ],
        'shipped' => [
            'subject' => 'Order on the way',
            'subject_line' => 'Order #:number shipped',
            'intro' => 'We handed over order #:number to the delivery service.',
            'next' => 'We will notify you as soon as it arrives.',
            'button' => 'Track order',
        ],
        'delivered' => [
            'subject' => 'Order delivered',
            'subject_line' => 'Order #:number delivered',
            'intro' => 'Order #:number has been delivered successfully.',
            'thanks' => 'We hope you enjoyed your purchase. Thank you for choosing :app!',
            'button' => 'View order',
        ],
        'status_updated' => [
            'subject_line' => 'Your order #:number status was updated',
        ],
    ],

    'api' => [
        'common' => [
            'not_found' => 'Resource not found.',
        ],
        'auth' => [
            'unauthenticated' => 'Unauthenticated.',
            'verification_link_sent' => 'Verification link sent.',
            'two_factor_required' => 'Two-factor authentication code required.',
            'invalid_two_factor_code' => 'Invalid two-factor authentication code.',
        ],
        'verify_email' => [
            'invalid_signature' => 'Invalid signature for email verification.',
            'already_verified' => 'Email address already verified.',
            'verified' => 'Email address verified.',
        ],
        'cart' => [
            'not_enough_stock' => 'Not enough stock',
            'coupon_not_found' => 'Coupon not found.',
            'coupon_not_applicable' => 'Coupon cannot be applied to this cart.',
            'points_auth_required' => 'Only authenticated users can redeem loyalty points.',
        ],
        'orders' => [
            'cart_empty' => 'Cart is empty',
            'insufficient_stock' => 'Insufficient stock for product #:product',
            'coupon_unavailable' => 'Coupon is no longer available.',
            'coupon_usage_limit_reached' => 'Coupon usage limit reached.',
            'not_enough_points' => 'Not enough loyalty points to redeem the requested amount.',
            'points_redeemed_description' => 'Points redeemed for order :number',
            'points_earned_description' => 'Points earned from order :number',
        ],
        'reviews' => [
            'submitted' => 'Review submitted for moderation.',
        ],
        'payments' => [
            'missing_intent' => 'Payment intent is missing.',
        ],
    ],

    'loyalty' => [
        'transaction' => [
            'earn' => 'Earned :points loyalty points.',
            'redeem' => 'Redeemed :points loyalty points.',
            'adjustment' => 'Points adjusted by :points.',
        ],
        'demo' => [
            'checkout_redeem' => 'Redeemed during checkout',
            'shipped_bonus' => 'Bonus for shipped order :number',
            'cancellation_return' => 'Points returned after cancellation',
        ],
    ],

    'security' => [
        'two_factor' => [
            'not_initialized' => 'Two-factor authentication is not initialized.',
            'invalid_code' => 'Invalid two-factor authentication code.',
            'enabled' => 'Two-factor authentication enabled.',
        ],
    ],
];
