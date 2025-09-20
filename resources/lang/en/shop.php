<?php

return [
    'admin' => [
        'brand' => 'Shop Admin',
        'navigation' => [
            'catalog' => 'Catalog',
            'sales' => 'Sales',
            'inventory' => 'Inventory',
            'settings' => 'Settings',
        ],
    ],

    'navigation' => [
        'catalog' => 'Catalog',
        'cart' => 'Cart',
        'order' => 'Order',
    ],

    'meta' => [
        'brand' => 'Shop',
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
        'name' => 'Name',
        'city' => 'City',
        'address' => 'Address',
        'postal_code' => 'Postal code',
        'note' => 'Note',
        'total' => 'Total',
        'tracking_number' => 'Tracking number',
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
        'sections' => [
            'general' => 'General',
            'shipping' => 'Shipping',
            'shipment' => 'Shipment',
            'summary' => 'Summary',
        ],
        'fieldsets' => [
            'shipping_address' => 'Shipping address',
            'billing_address' => 'Billing address',
        ],
        'fields' => [
            'user' => 'User',
            'number' => 'Number',
            'shipment_status' => 'Shipment status',
        ],
        'helpers' => [
            'email_auto' => 'If a user is selected, the email will be filled automatically.',
        ],
        'hints' => [
            'number_generated' => 'Generated automatically',
        ],
        'actions' => [
            'messages' => 'Messages',
            'mark_paid' => 'Mark paid',
            'mark_shipped' => 'Mark shipped',
            'cancel' => 'Cancel',
            'resend_confirmation' => 'Resend confirmation',
        ],
        'notifications' => [
            'marked_paid' => 'Order marked as paid',
            'marked_shipped' => 'Order marked as shipped',
            'cancelled' => 'Order canceled',
            'confirmation_resent' => 'Confirmation email resent',
        ],
        'summary' => [
            'positions' => 'Positions',
            'subtotal' => 'Subtotal',
            'total_order' => 'Total (order)',
        ],
        'shipment_status' => [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'shipped' => 'Shipped',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
        ],
        'statuses' => [
            'new' => 'new',
            'paid' => 'paid',
            'shipped' => 'shipped',
            'cancelled' => 'cancelled',
        ],
        'errors' => [
            'only_new_can_be_marked_paid' => 'Only orders with the ":required" status can be marked as paid. Order #:number is currently ":status".',
            'only_paid_can_be_marked_shipped' => 'Only orders with the ":required" status can be marked as shipped. Order #:number is currently ":status".',
            'only_new_or_paid_can_be_cancelled' => 'Only orders with the following statuses can be cancelled: :allowed. Order #:number is currently ":status".',
        ],
    ],

    'inventory' => [
        'not_enough_stock' => 'Not enough stock for product #:product_id at warehouse #:warehouse_id.',
        'not_enough_reserved_stock' => 'Not enough reserved stock for product #:product_id at warehouse #:warehouse_id.',
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
            'invalid_signature' => 'Invalid Stripe signature.',
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

    'products' => [
        'fields' => [
            'name' => 'Name',
            'slug' => 'Slug',
            'sku' => 'SKU',
            'category' => 'Category',
            'vendor' => 'Vendor',
            'preview' => 'Preview',
            'preview_url_debug' => 'URL?',
            'stock' => 'Stock',
            'price' => 'Price',
            'price_old' => 'Old price',
            'is_active' => 'Active',
        ],
        'attributes' => [
            'label' => 'Attributes',
            'name' => 'Name',
            'value' => 'Value',
            'add' => 'Add attribute',
        ],
        'placeholders' => [
            'available_stock' => 'Available stock',
        ],
        'filters' => [
            'category' => 'Category',
            'is_active' => [
                'label' => 'Active',
                'true' => 'Active',
                'false' => 'Inactive',
            ],
        ],
    ],

    'categories' => [
        'fields' => [
            'name' => 'Name',
            'slug' => 'Slug',
            'parent' => 'Parent category',
            'deleted_at' => 'Deleted at',
            'created_at' => 'Created at',
            'updated_at' => 'Updated at',
        ],
    ],

    'currencies' => [
        'navigation_group' => 'Settings',
        'code' => 'Code',
        'rate' => 'Rate',
        'rate_vs_base' => 'Rate (vs base)',
        'updated' => 'Updated',
    ],

    'security' => [
        'two_factor' => [
            'not_initialized' => 'Two-factor authentication is not initialized.',
            'invalid_code' => 'Invalid two-factor authentication code.',
            'enabled' => 'Two-factor authentication enabled.',
        ],
    ],
];
