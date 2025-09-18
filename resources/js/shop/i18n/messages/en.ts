const messages = {
    languageName: 'English',
    common: {
        brand: '3D-Print Shop',
        loading: 'Loading…',
        actions: {
            back: 'Back',
            retry: 'Retry',
        },
    },
    header: {
        brand: '3D-Print Shop',
        nav: {
            catalog: 'Catalog',
            cookies: 'Manage cookies',
        },
        account: {
            defaultName: 'My profile',
            profile: 'My profile',
            logout: 'Log out',
            login: 'Sign in',
            register: 'Sign up',
        },
    },
    search: {
        placeholder: 'Search products…',
        panel: {
            minQuery: ({ min }: { min: number }) => `Enter at least ${min} characters to search.`,
            loadError: 'Failed to load suggestions',
            showAll: ({ query }: { query: string }) => `Show all results for “${query}”`,
            empty: 'No results found',
        },
    },
    miniCart: {
        summary: {
            total: 'Total',
        },
        actions: {
            viewCart: 'View cart',
            checkout: 'Checkout',
        },
        empty: 'Your cart is empty',
    },
    cart: {
        seoTitle: ({ brand }: { brand: string }) => `Cart — ${brand}`,
        title: 'Cart',
        loading: 'Loading…',
        empty: {
            message: 'Your cart is empty.',
            cta: 'Go shopping',
        },
        vendor: {
            label: 'Seller',
            contact: 'Message the seller',
        },
        line: {
            remove: 'Remove',
        },
        summary: {
            totalLabel: 'Total',
            total: 'Amount due',
            checkout: 'Checkout',
        },
    },
    checkout: {
        seoTitle: ({ brand }: { brand: string }) => `Checkout — ${brand}`,
        title: 'Checkout',
        steps: {
            address: 'Address',
            delivery: 'Delivery',
            payment: 'Payment',
        },
        notifications: {
            cartUnavailable: 'Your cart is empty or already checked out.',
            cartCheckFailed: 'We could not verify your cart.',
            addressesLoadFailed: 'Failed to load addresses.',
            couponApplyFailed: 'Could not apply the coupon.',
            couponApplied: 'Coupon applied.',
            couponRemoved: 'Coupon removed.',
            orderCreateSuccess: 'Order created. Complete the payment.',
            orderCreateFailed: 'Could not create the order.',
        },
        address: {
            emailLabel: 'Contact email',
            emailPlaceholder: 'you@example.com',
            saved: {
                title: 'Saved addresses',
                emptyAuthenticated: 'You have no saved addresses yet.',
                emptyGuest: 'Sign in to use saved addresses.',
            },
            fields: {
                name: {
                    label: 'Recipient name',
                    placeholder: 'First and last name',
                },
                city: {
                    label: 'City',
                    placeholder: 'Kyiv',
                },
                addr: {
                    label: 'Shipping address',
                    placeholder: 'Shevchenka St, 1',
                },
                postal: {
                    optionalLabel: 'Postal code (optional)',
                    placeholder: '01001',
                },
                phone: {
                    optionalLabel: 'Phone (optional)',
                    placeholder: '+380 00 000 0000',
                },
            },
            next: 'Continue to delivery',
        },
        billing: {
            toggle: 'Need invoice details',
            description: 'Provide billing details for invoices and documents.',
            copyFromShipping: 'Copy from shipping',
            fields: {
                name: {
                    label: 'Name / contact person',
                    placeholder: 'First and last name',
                },
                company: {
                    label: 'Company (optional)',
                    placeholder: 'Example LLC',
                },
                taxId: {
                    label: 'Tax number (VAT / EIN)',
                    placeholder: '1234567890',
                },
                city: {
                    label: 'City',
                    placeholder: 'Kyiv',
                },
                addr: {
                    label: 'Billing address',
                    placeholder: 'Shevchenka St, 1',
                },
                postal: {
                    optionalLabel: 'Postal code (optional)',
                    placeholder: '01001',
                },
            },
        },
        errors: {
            emailRequired: 'Enter an email for confirmation.',
            emailInvalid: 'Enter a valid email address.',
            shippingNameRequired: 'Enter the recipient name.',
            shippingCityRequired: 'Enter the delivery city.',
            shippingAddrRequired: 'Enter the delivery address.',
            billingNameRequired: 'Enter the billing name.',
            billingCityRequired: 'Enter the billing city.',
            billingAddrRequired: 'Enter the billing address.',
            billingTaxRequired: 'Enter the company tax number.',
        },
        delivery: {
            title: 'Delivery method',
            commentLabel: 'Courier comment (optional)',
            commentPlaceholder: 'For example, call 30 minutes before delivery',
            options: {
                nova: {
                    title: 'Nova Poshta',
                    description: 'Delivery within Ukraine in 2–3 days.',
                },
                ukr: {
                    title: 'Ukrposhta',
                    description: 'Economy delivery 3–5 days to a branch.',
                },
                pickup: {
                    title: 'Pickup',
                    description: 'Collect your order today in our workshop (Kyiv).',
                },
            },
        },
        coupon: {
            title: 'Coupon',
            placeholder: 'Enter coupon code',
            applying: 'Applying…',
            apply: 'Apply',
            applied: ({ code }: { code: string }) => `Coupon applied: ${code}`,
        },
        summary: {
            title: 'Your order',
            quantity: ({ count }: { count: number }) => `Quantity: ${count}`,
            subtotal: 'Items subtotal',
            discount: 'Discount',
            total: 'Total due',
            notice: 'After you proceed to payment you will need to create a new order to change shipping or delivery.',
            goToPayment: 'Proceed to payment',
            creating: 'Creating…',
        },
        payment: {
            preparing: 'Preparing payment…',
            orderNumberLabel: 'Order number',
            confirmationNotice: ({ email }: { email: string }) => `Confirmation will be sent to ${email}.`,
            totalNotice: ({ amount }: { amount: string }) => `Amount due: ${amount}`,
            title: 'Payment',
            description: 'Secure payment via Stripe. After a successful transaction you will be redirected to the order confirmation.',
            billingTitle: 'Billing details',
            billingTax: ({ taxId }: { taxId: string }) => `Tax number: ${taxId}`,
            billingMatchesShipping: 'Billing details match the shipping address.',
            shippingTitle: 'Shipping',
            shippingMethod: ({ method }: { method: string }) => `Delivery method: ${method}`,
            shippingComment: ({ comment }: { comment: string }) => `Comment: ${comment}`,
            itemsTitle: 'Items',
        },
        notes: {
            delivery: ({ method }: { method: string }) => `Delivery: ${method}`,
            comment: ({ comment }: { comment: string }) => `Comment: ${comment}`,
        },
    },
} as const;

export default messages;
