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
    catalog: {
        seo: {
            listName: ({ category }: { category?: string }) => category ? `Catalog — ${category}` : 'Catalog',
            documentTitle: ({ category, query }: { category?: string; query?: string }) => {
                const parts = ['Catalog'];
                if (category) parts.push(category);
                if (query) parts.push(`search “${query}”`);
                return parts.join(' — ');
            },
            pageTitle: ({ category, query, brand }: { category?: string; query?: string; brand: string }) => {
                const parts = ['Catalog'];
                if (category) parts.push(category);
                if (query) parts.push(`search “${query}”`);
                return `${parts.join(' — ')} — ${brand}`;
            },
            description: ({ category, query }: { category?: string; query?: string }) => [
                'Online store catalog. Filters: category, color, size, price.',
                category ? `Category: ${category}.` : '',
                query ? `Search: ${query}.` : '',
            ].filter(Boolean).join(' '),
            breadcrumbHome: 'Home',
            breadcrumbCatalog: 'Catalog',
        },
        header: {
            title: 'Catalog',
            categoryPlaceholder: 'Category',
            allCategories: 'All categories',
            sort: {
                new: 'New arrivals',
                priceAsc: 'Price ↑',
                priceDesc: 'Price ↓',
            },
        },
        filters: {
            searchPlaceholder: 'Search products…',
            priceMinPlaceholder: 'Price from',
            priceMaxPlaceholder: 'to',
            applyPrice: 'Apply',
            clearAll: 'Clear all',
            active: {
                color: ({ value }: { value: string }) => `Color: ${value}`,
                size: ({ value }: { value: string }) => `Size: ${value}`,
                minPrice: ({ value }: { value: number }) => `From: ${value}`,
                maxPrice: ({ value }: { value: number }) => `To: ${value}`,
                clearTooltip: 'Clear this filter',
                clearAll: 'Clear all',
            },
            facets: {
                categories: 'Categories',
                colors: 'Color',
                sizes: 'Size',
                empty: 'no data',
            },
        },
        products: {
            empty: 'Nothing found. Try adjusting the filters.',
        },
        cards: {
            noImage: 'no image',
            outOfStock: 'Out of stock',
            adding: 'Buying…',
            addToCart: 'Add to cart',
        },
        pagination: {
            prev: 'Previous',
            next: 'Next',
            pageStatus: ({ page, lastPage }: { page: number; lastPage: number }) => `Page ${page} of ${lastPage}`,
        },
    },
    profile: {
        navigation: {
            overview: 'Profile',
            orders: 'My orders',
            addresses: 'Saved addresses',
            points: 'Loyalty points',
        },
        overview: {
            loading: 'Loading profile…',
            title: 'Profile',
            welcome: ({ name }: { name: string }) =>
                `Welcome, ${name}. Manage your details and explore other profile sections.`,
            guestName: 'guest',
            personalDataTitle: 'Personal details',
            verification: {
                title: 'Email not verified.',
                description: 'Check your inbox or resend the confirmation email.',
                resend: {
                    sending: 'Sending…',
                    action: 'Resend confirmation email',
                },
                successFallback: 'Confirmation email sent again.',
                errorFallback: 'Failed to send the verification email. Please try again.',
            },
            form: {
                labels: {
                    name: 'Name',
                    email: 'Email',
                    newPassword: 'New password',
                    confirmPassword: 'Confirm password',
                },
                placeholders: {
                    name: 'Enter your name',
                    email: 'your@email.com',
                    newPassword: 'Leave blank to keep current',
                    confirmPassword: 'Repeat the new password',
                },
                hintPasswordOptional:
                    'You can leave the password blank if you do not plan to change it. The email must be unique.',
                hintApplyImmediately: 'Changes take effect immediately after saving.',
                submit: {
                    saving: 'Saving…',
                    save: 'Save changes',
                },
            },
            info: {
                id: 'ID',
                name: 'Name',
                email: 'Email',
                verified: 'Email verified',
                verifiedYes: 'Yes',
                verifiedNo: 'No',
            },
            session: {
                tokenNote: 'The Sanctum token is stored locally for authenticated API requests.',
                logout: {
                    processing: 'Signing out…',
                    action: 'Sign out',
                    error: 'Failed to sign out. Please try again.',
                },
            },
            notifications: {
                updateSuccess: 'Profile details updated.',
            },
            errors: {
                update: 'Failed to update the profile. Please try again.',
                loadTwoFactorStatus: 'Failed to load the two-factor authentication status.',
                startTwoFactor: 'Failed to start the two-factor authentication setup.',
                confirmTwoFactor: 'Failed to confirm the code. Please try again.',
                disableTwoFactor: 'Failed to disable two-factor authentication.',
                resendVerification: 'Failed to send the verification email. Please try again.',
            },
            twoFactor: {
                title: 'Two-factor authentication',
                statusLabel: 'Status:',
                status: {
                    enabled: 'Enabled',
                    pending: 'Pending confirmation',
                    disabled: 'Disabled',
                },
                confirmedAtLabel: 'Confirmed:',
                description: 'Two-factor authentication adds an extra layer of security to your account.',
                loadingStatus: 'Loading status…',
                secret: {
                    title: 'Secret key',
                    instructions:
                        'Add this key to your authenticator app (Google Authenticator, 1Password, Authy, etc.). You can also open the setup directly using the link below.',
                    openApp: 'Open in the app',
                },
                confirm: {
                    codeLabel: 'Confirmation code',
                    codePlaceholder: 'Enter the app code',
                    helper: 'Enter the six-digit code from your authenticator app to finish the setup.',
                    submit: 'Confirm',
                    submitting: 'Confirming…',
                    cancel: 'Cancel',
                },
                callouts: {
                    pendingSetup: 'The previous setup was not completed. You can generate a new secret key to start over.',
                },
                enable: {
                    action: 'Enable 2FA',
                    loading: 'Please wait…',
                },
                disable: {
                    action: 'Disable 2FA',
                    confirm: 'Are you sure you want to disable two-factor authentication?',
                },
                messages: {
                    enabled: 'Two-factor authentication enabled.',
                    disabled: 'Two-factor authentication disabled.',
                    emptyCode: 'Enter the confirmation code from the app.',
                },
            },
        },
        orders: {
            loading: 'Loading orders…',
            title: 'My orders',
            description: 'Review your purchase history, track order statuses, and open detailed information.',
            error: 'Failed to load orders.',
            table: {
                loading: 'Loading…',
                empty: {
                    description: 'You have not placed any orders yet.',
                    cta: 'Browse the catalog',
                },
                headers: {
                    number: 'Number',
                    date: 'Date',
                    status: 'Status',
                    total: 'Total',
                    actions: 'Actions',
                },
                view: 'View order details',
            },
        },
        addresses: {
            loading: 'Loading addresses…',
            title: 'Saved addresses',
            description: 'Manage delivery addresses to speed up future checkouts.',
            error: 'Failed to load addresses.',
            list: {
                loading: 'Loading…',
                empty: 'You do not have any saved addresses yet. Add one during checkout.',
                defaultName: 'Untitled',
                fields: {
                    city: 'City',
                    address: 'Address',
                    postalCode: 'Postal code',
                    phone: 'Phone',
                },
            },
        },
        points: {
            loading: 'Loading points…',
            title: 'Loyalty points',
            description: 'Track your available balance and loyalty point history.',
            error: 'Failed to load loyalty information.',
            type: {
                default: 'Transaction',
                earn: 'Earned',
                redeem: 'Redeemed',
            },
            stats: {
                balance: 'Available',
                earned: 'Earned',
                spent: 'Redeemed',
            },
            table: {
                loading: 'Loading…',
                empty: 'No loyalty history yet. Use points during checkout to see the activity here.',
                headers: {
                    date: 'Date',
                    description: 'Description',
                    type: 'Type',
                    amount: 'Amount',
                },
                type: {
                    default: 'Transaction',
                    earn: 'Earned',
                    redeem: 'Redeemed',
                },
            },
        },
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
    order: {
        confirmation: {
            loading: 'Loading…',
            notFound: 'Order not found.',
            seoTitle: ({ number, brand }: { number: string; brand: string }) => `Order ${number} — ${brand}`,
            title: ({ number }: { number: string }) => `Thank you! Order ${number} is confirmed`,
            confirmationNotice: ({ email }: { email: string }) => `Confirmation sent to ${email}.`,
            paymentPending: 'Payment pending.',
            chat: {
                open: 'Message the seller',
                close: 'Hide chat',
            },
            shipping: {
                title: 'Shipping & tracking',
                trackingNumber: 'Tracking number:',
                pending: 'Pending',
            },
            billing: {
                title: 'Billing details',
                taxIdLabel: 'Tax number:',
            },
            table: {
                product: 'Product',
                quantity: 'Qty',
                price: 'Price',
                total: 'Total',
                viewProduct: 'View product',
                vendor: 'Seller:',
                contactSeller: 'Message the seller',
                subtotal: 'Items subtotal',
                coupon: 'Coupon',
                discount: 'Discount',
                loyalty: 'Used points',
                loyaltyValue: ({ amount }: { amount: string }) => `(−${amount})`,
                amountDue: 'Total due',
            },
            cta: {
                continue: 'Continue shopping',
            },
            payment: {
                title: 'Order payment',
                description: 'Secure via Stripe. Cards and local methods (EU) are available.',
            },
        },
    },
    auth: {
        shared: {
            loading: 'Loading…',
            processing: 'Please wait…',
        },
        register: {
            title: 'Sign up',
            nameLabel: 'Name',
            emailLabel: 'Email',
            passwordLabel: 'Password',
            passwordConfirmationLabel: 'Confirm password',
            submit: 'Create account',
            haveAccount: 'Already have an account?',
            signInLink: 'Sign in',
            passwordMismatch: 'Passwords do not match.',
            errorFallback: 'Could not sign up. Please try again.',
        },
        login: {
            title: 'Sign in',
            emailLabel: 'Email',
            passwordLabel: 'Password',
            forgotPassword: 'Forgot password?',
            submit: 'Sign in',
            noAccount: 'Don’t have an account?',
            registerLink: 'Create one',
            errorFallback: 'Could not sign in. Please try again.',
            otpRequired: 'A one-time code is required. Enter the code from your authenticator app.',
            otpLabel: 'Verification code',
            otpPlaceholder: 'For example, 123456',
            otpHelp: 'Use your authenticator app to get a six-digit code.',
        },
    },
    wishlist: {
        title: 'Wishlist',
        clear: 'Clear',
        loading: 'Refreshing your wishlist…',
        errorTitle: 'Could not update the list',
        empty: 'Nothing here yet.',
        removeAria: ({ name }: { name: string }) => `Remove “${name}” from wishlist`,
        noImage: 'no image',
    },
    product: {
        reviewForm: {
            ariaLabel: 'Review form',
            title: 'Leave a review',
            authPromptPrefix: 'To share your impressions,',
            authPromptLogin: 'sign in',
            authPromptMiddle: 'or',
            authPromptRegister: 'sign up',
            authPromptSuffix: '.',
            formErrorUnauthenticated: 'Please sign in to leave a review.',
            successTitle: 'Thank you for your review!',
            successDescription: 'Your review will be published after moderation.',
            errorFallback: 'Could not submit the review. Please try again later.',
            errorTitle: 'Failed to submit the review',
            ratingLabel: 'Rating',
            commentLabel: 'Comment (optional)',
            commentPlaceholder: 'Share your experience with the product',
            submitting: 'Sending…',
            submit: 'Submit review',
        },
    },
    notify: {
        cart: {
            add: {
                success: 'Added to cart',
                action: 'Open cart',
                outOfStock: 'Not enough stock',
                error: 'Could not add to cart',
            },
            update: {
                success: 'Quantity updated',
                outOfStock: 'Not enough stock',
                error: 'Cart update failed',
            },
            remove: {
                success: 'Removed from cart',
            },
        },
    },
} as const;

export default messages;
