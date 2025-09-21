<?php

return [
    'admin' => [
        'brand' => 'Shop Admin',
        'navigation' => [
            'catalog' => 'Catalog',
            'sales' => 'Sales',
            'accounting' => 'Accounting',
            'inventory' => 'Inventory',
            'marketing' => 'Marketing',
            'content' => 'Content',
            'customers' => 'Customers',
            'settings' => 'Settings',
        ],
        'language_switcher' => [
            'label' => 'Interface language',
            'help' => 'Changes the panel language for your next page load.',
        ],
        'dashboard' => [
            'filters' => [
                'period' => 'Period',
                'today' => 'Today',
                'seven_days' => 'Last 7 days',
                'thirty_days' => 'Last 30 days',
                'ninety_days' => 'Last 90 days',
            ],
            'sales' => [
                'title' => 'Sales performance',
                'revenue' => 'Revenue',
                'orders' => 'Orders',
                'average_order_value' => 'Average order value',
            ],
            'conversion' => [
                'title' => 'Checkout conversion',
                'rate' => 'Conversion rate',
                'rate_help' => 'Orders vs carts for the selected period.',
                'orders' => 'Orders',
                'carts' => 'Carts',
            ],
            'traffic' => [
                'title' => 'Traffic sources',
                'revenue' => 'Revenue share',
            ],
            'top_products' => [
                'title' => 'Top products',
                'columns' => [
                    'product' => 'Product',
                    'sku' => 'SKU',
                    'quantity' => 'Units sold',
                    'revenue' => 'Revenue',
                ],
            ],
            'inventory' => [
                'title' => 'Inventory status',
                'skus' => 'Tracked SKUs',
                'available_units' => 'Available units',
                'low_stock' => 'Low stock (≤ :threshold)',
            ],
        ],
        'resources' => [
            'products' => [
                'label' => 'Product',
                'plural_label' => 'Products',
                'imports' => [
                    'tabs' => [
                        'form' => 'Import products',
                        'history' => 'History',
                    ],
                    'form' => [
                        'heading' => 'Upload spreadsheet',
                        'actions' => [
                            'queue' => 'Queue import',
                        ],
                    ],
                    'fields' => [
                        'file' => 'Import file',
                    ],
                    'messages' => [
                        'missing_file' => 'Please select a file to import.',
                        'queued_title' => 'Product import started',
                        'queued_body' => 'The import will run in the background. You will be notified when it finishes.',
                        'completed_title' => 'Product import completed',
                        'completed_body' => 'Processed :processed of :total rows.',
                        'failed_title' => 'Product import failed',
                        'no_rows' => 'No data rows were detected in the uploaded file.',
                        'row_created' => 'Product created (:sku)',
                        'row_updated' => 'Product updated (:sku)',
                    ],
                    'table' => [
                        'recent_imports' => 'Recent imports',
                        'recent_exports' => 'Recent exports',
                        'columns' => [
                            'file' => 'File',
                            'status' => 'Status',
                            'progress' => 'Progress',
                            'results' => 'Results',
                            'completed_at' => 'Completed at',
                            'format' => 'Format',
                            'rows' => 'Rows',
                        ],
                        'results_created' => 'Created',
                        'results_updated' => 'Updated',
                        'results_failed' => 'Failed',
                        'empty_imports' => 'No imports yet.',
                        'empty_exports' => 'No exports yet.',
                    ],
                ],
                'exports' => [
                    'tabs' => [
                        'form' => 'Export products',
                        'history' => 'History',
                    ],
                    'form' => [
                        'heading' => 'Configure export',
                        'actions' => [
                            'queue' => 'Queue export',
                        ],
                    ],
                    'fields' => [
                        'file_name' => 'File name',
                        'format' => 'Format',
                        'only_active' => 'Only active products',
                    ],
                    'messages' => [
                        'queued_title' => 'Product export started',
                        'queued_body' => 'The export will run in the background. You will be notified when it finishes.',
                        'completed_title' => 'Product export completed',
                        'completed_empty' => 'No products matched the selected filters.',
                        'completed_ready' => 'Products export is ready to download.',
                        'failed_title' => 'Product export failed',
                        'download' => 'Download',
                        'pending' => 'Pending',
                    ],
                    'table' => [
                        'recent_exports' => 'Recent exports',
                        'recent_imports' => 'Recent imports',
                        'columns' => [
                            'format' => 'Format',
                            'status' => 'Status',
                            'rows' => 'Rows',
                            'completed_at' => 'Completed at',
                            'file' => 'File',
                        ],
                        'empty_exports' => 'No exports yet.',
                        'empty_imports' => 'No imports yet.',
                    ],
                ],
            ],
            'categories' => [
                'label' => 'Category',
                'plural_label' => 'Categories',
            ],
            'orders' => [
                'label' => 'Order',
                'plural_label' => 'Orders',
            ],
            'vendors' => [
                'label' => 'Vendor',
                'plural_label' => 'Vendors',
            ],
            'inventory' => [
                'label' => 'Inventory item',
                'plural_label' => 'Inventory',
            ],
            'coupons' => [
                'label' => 'Coupon',
                'plural_label' => 'Coupons',
            ],
            'reviews' => [
                'label' => 'Review',
                'plural_label' => 'Reviews',
            ],
            'users' => [
                'label' => 'Customer',
                'plural_label' => 'Customers',
            ],
            'warehouses' => [
                'label' => 'Warehouse',
                'plural_label' => 'Warehouses',
            ],
            'currencies' => [
                'label' => 'Currency',
                'plural_label' => 'Currencies',
            ],
            'invoices' => [
                'label' => 'Invoice',
                'plural_label' => 'Invoices',
                'fields' => [
                    'number' => 'Number',
                    'issued_at' => 'Issued at',
                    'due_at' => 'Due at',
                    'subtotal' => 'Subtotal',
                    'tax_total' => 'Tax total',
                    'metadata' => 'Metadata',
                ],
            ],
            'delivery_notes' => [
                'label' => 'Delivery note',
                'plural_label' => 'Delivery notes',
                'fields' => [
                    'number' => 'Number',
                    'issued_at' => 'Issued at',
                    'dispatched_at' => 'Dispatched at',
                    'items' => 'Items',
                    'remarks' => 'Remarks',
                ],
            ],
            'acts' => [
                'label' => 'Act',
                'plural_label' => 'Acts',
                'fields' => [
                    'number' => 'Number',
                    'issued_at' => 'Issued at',
                    'total' => 'Total',
                    'description' => 'Description',
                ],
            ],
            'saft_exports' => [
                'label' => 'SAF-T export',
                'plural_label' => 'SAF-T exports',
                'fields' => [
                    'format' => 'Format',
                    'exported_at' => 'Exported at',
                    'created_at' => 'Created at',
                    'message' => 'Message',
                    'from_date' => 'From date',
                    'to_date' => 'To date',
                ],
                'status' => [
                    'completed' => 'Completed',
                    'processing' => 'Processing',
                    'failed' => 'Failed',
                ],
                'actions' => [
                    'export' => 'SAF-T export',
                    'run' => 'Start export',
                    'view_logs' => 'View logs',
                ],
                'messages' => [
                    'completed' => '{0} SAF-T export generated with no matching orders|{1} SAF-T export generated for :count order|[2,*] SAF-T export generated for :count orders',
                    'success' => 'SAF-T export started successfully.',
                    'completed_info' => 'Once finished you can download the export from the logs list.',
                    'latest_title' => 'Latest export',
                ],
            ],
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

    'languages' => [
        'uk' => 'Ukrainian',
        'en' => 'English',
        'ru' => 'Russian',
        'pt' => 'Portuguese',
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
        'created' => 'Created',
        'updated' => 'Updated',
        'add' => 'Add',
        'download' => 'Download',
        'export' => 'Export',
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
            'total' => 'Total',
            'shipment_status' => 'Shipment status',
            'currency' => 'Currency',
        ],
        'helpers' => [
            'email_auto' => 'If a user is selected, the email will be filled automatically.',
        ],
        'placeholders' => [
            'any_order' => 'Any order',
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
        'items' => [
            'title' => 'Order items',
            'fields' => [
                'product' => 'Product',
                'qty' => 'Quantity',
                'price' => 'Price',
                'subtotal' => 'Subtotal',
            ],
            'empty_state' => [
                'heading' => 'No items',
            ],
        ],
        'logs' => [
            'title' => 'Status history',
            'fields' => [
                'from' => 'From',
                'to' => 'To',
                'by' => 'By',
                'note' => 'Note',
                'deleted_at' => 'Deleted at',
                'created_at' => 'Created at',
                'updated_at' => 'Updated at',
            ],
            'empty_state' => [
                'heading' => 'No status changes yet',
            ],
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
        'fields' => [
            'product' => 'Product',
            'warehouse' => 'Warehouse',
            'quantity' => 'Quantity',
            'reserved' => 'Reserved',
            'available' => 'Available',
        ],
        'filters' => [
            'warehouse' => 'Warehouse',
        ],
    ],

    'warehouses' => [
        'fields' => [
            'code' => 'Code',
            'name' => 'Name',
            'description' => 'Description',
        ],
        'columns' => [
            'created' => 'Created',
            'updated' => 'Updated',
        ],
    ],

    'coupons' => [
        'fields' => [
            'code' => 'Code',
            'type' => 'Type',
            'value' => 'Value',
            'min_cart' => 'Min cart',
            'max_discount' => 'Max discount',
            'usage' => 'Usage',
            'usage_limit' => 'Total usage limit',
            'per_user_limit' => 'Per-user limit',
            'starts_at' => 'Starts at',
            'expires_at' => 'Expires at',
            'is_active' => 'Active',
        ],
        'filters' => [
            'is_active' => 'Active',
        ],
        'helpers' => [
            'code_unique' => 'Unique coupon code customers will enter.',
        ],
        'types' => [
            'fixed' => 'Fixed amount',
            'percent' => 'Percentage',
        ],
    ],

    'reviews' => [
        'fields' => [
            'product' => 'Product',
            'user' => 'User',
            'rating' => 'Rating',
            'status' => 'Status',
            'text' => 'Review text',
            'created_at' => 'Created',
        ],
        'filters' => [
            'status' => 'Status',
        ],
        'statuses' => [
            'pending' => 'Pending',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ],
    ],

    'users' => [
        'fields' => [
            'points_balance' => 'Points balance',
            'password' => 'Password',
            'roles' => 'Roles',
            'categories' => 'Allowed categories',
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
        'transactions' => [
            'fields' => [
                'type' => 'Type',
                'points' => 'Points',
                'amount' => 'Amount',
                'description' => 'Description',
            ],
            'types' => [
                'earn' => 'Earned',
                'redeem' => 'Redeemed',
                'adjustment' => 'Adjustment',
            ],
        ],
    ],

    'widgets' => [
        'orders_stats' => [
            'labels' => [
                'new' => 'New',
                'paid' => 'Paid',
                'shipped' => 'Shipped',
                'cancelled' => 'Cancelled',
            ],
            'descriptions' => [
                'new' => 'Awaiting',
            ],
        ],
    ],

    'products' => [
        'fields' => [
            'name' => 'Name',
            'slug' => 'Slug',
            'description' => 'Description',
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
            'translations' => 'Translations',
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
        'images' => [
            'title' => 'Images',
            'fields' => [
                'image' => 'Image',
                'alt_text' => 'Alt text',
                'is_primary' => 'Primary image',
                'preview' => 'Preview',
                'disk' => 'Disk',
                'sort' => 'Sort order',
                'created_at' => 'Created at',
            ],
            'helper_texts' => [
                'is_primary' => 'Used as the product preview.',
            ],
            'actions' => [
                'create' => 'Add image',
                'edit' => 'Edit image',
                'delete' => 'Delete image',
            ],
            'empty' => [
                'heading' => 'No images yet',
                'description' => 'Upload product images to see them here.',
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

    'vendor' => [
        'fields' => [
            'name' => 'Name',
            'slug' => 'Slug',
            'description' => 'Description',
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
