const messages = {
    languageName: 'Українська',
    common: {
        brand: '3D-Print Shop',
        loading: 'Завантаження…',
        actions: {
            back: 'Назад',
            retry: 'Повторити',
        },
    },
    header: {
        brand: '3D-Print Shop',
        nav: {
            catalog: 'Каталог',
            cookies: 'Налаштувати cookies',
        },
        account: {
            defaultName: 'Мій профіль',
            profile: 'Мій профіль',
            logout: 'Вийти',
            login: 'Увійти',
            register: 'Зареєструватися',
        },
    },
    search: {
        placeholder: 'Пошук товарів…',
        panel: {
            minQuery: ({ min }: { min: number }) => `Введіть щонайменше ${min} символи для пошуку.`,
            loadError: 'Не вдалося завантажити підказки',
            showAll: ({ query }: { query: string }) => `Показати всі результати для “${query}”`,
            empty: 'Нічого не знайдено',
        },
    },
    miniCart: {
        summary: {
            total: 'Разом',
        },
        actions: {
            viewCart: 'Відкрити кошик',
            checkout: 'Оформити',
        },
        empty: 'Кошик порожній',
    },
    cart: {
        seoTitle: ({ brand }: { brand: string }) => `Кошик — ${brand}`,
        title: 'Кошик',
        loading: 'Завантаження…',
        empty: {
            message: 'Кошик порожній.',
            cta: 'Перейти до покупок',
        },
        vendor: {
            label: 'Продавець',
            contact: 'Написати продавцю',
        },
        line: {
            remove: 'Прибрати',
        },
        summary: {
            totalLabel: 'Разом',
            total: 'До сплати',
            checkout: 'Оформити',
        },
    },
    checkout: {
        seoTitle: ({ brand }: { brand: string }) => `Оформлення замовлення — ${brand}`,
        title: 'Оформлення замовлення',
        steps: {
            address: 'Адреса',
            delivery: 'Доставка',
            payment: 'Оплата',
        },
        notifications: {
            cartUnavailable: 'Кошик порожній або вже оформлено.',
            cartCheckFailed: 'Не вдалося перевірити кошик.',
            addressesLoadFailed: 'Не вдалося завантажити адреси.',
            couponApplyFailed: 'Не вдалося застосувати купон.',
            couponApplied: 'Купон застосовано.',
            couponRemoved: 'Купон скасовано.',
            orderCreateSuccess: 'Замовлення створено. Завершіть оплату.',
            orderCreateFailed: 'Не вдалося створити замовлення.',
        },
        address: {
            emailLabel: 'Контактний email',
            emailPlaceholder: 'you@example.com',
            saved: {
                title: 'Збережені адреси',
                emptyAuthenticated: 'У вас ще немає збережених адрес.',
                emptyGuest: 'Увійдіть, щоб використовувати збережені адреси.',
            },
            fields: {
                name: {
                    label: 'Імʼя одержувача',
                    placeholder: 'Імʼя Прізвище',
                },
                city: {
                    label: 'Місто',
                    placeholder: 'Київ',
                },
                addr: {
                    label: 'Адреса доставки',
                    placeholder: 'вул. Шевченка, 1',
                },
                postal: {
                    optionalLabel: 'Поштовий індекс (необовʼязково)',
                    placeholder: '01001',
                },
                phone: {
                    optionalLabel: 'Телефон (необовʼязково)',
                    placeholder: '+380 00 000 0000',
                },
            },
            next: 'До доставки',
        },
        billing: {
            toggle: 'Потрібні реквізити для рахунку',
            description: 'Вкажіть платіжні дані для рахунку та документів.',
            copyFromShipping: 'Заповнити як для доставки',
            fields: {
                name: {
                    label: 'Імʼя / відповідальна особа',
                    placeholder: 'Імʼя Прізвище',
                },
                company: {
                    label: 'Компанія (необовʼязково)',
                    placeholder: 'ТОВ «Приклад»',
                },
                taxId: {
                    label: 'Податковий номер (ІПН / VAT)',
                    placeholder: '1234567890',
                },
                city: {
                    label: 'Місто',
                    placeholder: 'Київ',
                },
                addr: {
                    label: 'Адреса платника',
                    placeholder: 'вул. Шевченка, 1',
                },
                postal: {
                    optionalLabel: 'Поштовий індекс (необовʼязково)',
                    placeholder: '01001',
                },
            },
        },
        errors: {
            emailRequired: 'Вкажіть email для підтвердження.',
            emailInvalid: 'Вкажіть коректну електронну адресу.',
            shippingNameRequired: 'Вкажіть імʼя одержувача.',
            shippingCityRequired: 'Вкажіть місто доставки.',
            shippingAddrRequired: 'Вкажіть адресу доставки.',
            billingNameRequired: 'Вкажіть імʼя платника.',
            billingCityRequired: 'Вкажіть місто платника.',
            billingAddrRequired: 'Вкажіть адресу платника.',
            billingTaxRequired: 'Вкажіть податковий номер компанії.',
        },
        delivery: {
            title: 'Спосіб доставки',
            commentLabel: 'Коментар курʼєру (необовʼязково)',
            commentPlaceholder: 'Наприклад, дзвоніть за 30 хвилин до доставки',
            options: {
                nova: {
                    title: 'Нова пошта',
                    description: 'Доставка протягом 2–3 днів по Україні.',
                },
                ukr: {
                    title: 'Укрпошта',
                    description: 'Економна доставка 3–5 днів до відділення.',
                },
                pickup: {
                    title: 'Самовивіз',
                    description: 'Заберіть замовлення сьогодні у нашій майстерні (Київ).',
                },
            },
        },
        coupon: {
            title: 'Купон',
            placeholder: 'Введіть код купона',
            applying: 'Застосування…',
            apply: 'Застосувати',
            applied: ({ code }: { code: string }) => `Застосовано купон: ${code}`,
        },
        summary: {
            title: 'Ваше замовлення',
            quantity: ({ count }: { count: number }) => `Кількість: ${count}`,
            subtotal: 'Сума товарів',
            discount: 'Знижка',
            total: 'До оплати',
            notice: 'Після переходу до оплати змінити адресу або доставку буде неможливо без створення нового замовлення.',
            goToPayment: 'До оплати',
            creating: 'Створення…',
        },
        payment: {
            preparing: 'Підготовка оплати…',
            orderNumberLabel: 'Номер замовлення',
            confirmationNotice: ({ email }: { email: string }) => `Підтвердження буде надіслано на ${email}.`,
            totalNotice: ({ amount }: { amount: string }) => `Сума до оплати: ${amount}`,
            title: 'Оплата',
            description: 'Безпечна оплата через Stripe. Після успішної транзакції ви будете перенаправлені до підтвердження замовлення.',
            billingTitle: 'Платіжні дані',
            billingTax: ({ taxId }: { taxId: string }) => `Податковий номер: ${taxId}`,
            billingMatchesShipping: 'Реквізити для рахунку збігаються з адресою доставки.',
            shippingTitle: 'Доставка',
            shippingMethod: ({ method }: { method: string }) => `Спосіб доставки: ${method}`,
            shippingComment: ({ comment }: { comment: string }) => `Коментар: ${comment}`,
            itemsTitle: 'Товари',
        },
        notes: {
            delivery: ({ method }: { method: string }) => `Доставка: ${method}`,
            comment: ({ comment }: { comment: string }) => `Коментар: ${comment}`,
        },
    },
    order: {
        confirmation: {
            loading: 'Завантаження…',
            notFound: 'Замовлення не знайдено.',
            seoTitle: ({ number, brand }: { number: string; brand: string }) => `Замовлення ${number} — ${brand}`,
            title: ({ number }: { number: string }) => `Дякуємо! Замовлення ${number} оформлено`,
            confirmationNotice: ({ email }: { email: string }) => `Підтвердження надіслано на ${email}.`,
            paymentPending: 'Оплата очікується.',
            chat: {
                open: 'Написати продавцю',
                close: 'Сховати чат',
            },
            shipping: {
                title: 'Доставка та відстеження',
                trackingNumber: 'Номер відстеження:',
                pending: 'Очікується',
            },
            billing: {
                title: 'Платіжні дані',
                taxIdLabel: 'Податковий номер:',
            },
            table: {
                product: 'Товар',
                quantity: 'К-сть',
                price: 'Ціна',
                total: 'Сума',
                viewProduct: 'Переглянути товар',
                vendor: 'Продавець:',
                contactSeller: 'Написати продавцю',
                subtotal: 'Разом за товари',
                coupon: 'Купон',
                discount: 'Знижка',
                loyalty: 'Використані бали',
                loyaltyValue: ({ amount }: { amount: string }) => `(−${amount})`,
                amountDue: 'До сплати',
            },
            cta: {
                continue: 'Продовжити покупки',
            },
            payment: {
                title: 'Оплата замовлення',
                description: 'Безпечно через Stripe. Доступні картки та локальні методи (EU).',
            },
        },
    },
    auth: {
        shared: {
            loading: 'Завантаження…',
            processing: 'Зачекайте…',
        },
        register: {
            title: 'Реєстрація',
            nameLabel: 'Імʼя',
            emailLabel: 'Email',
            passwordLabel: 'Пароль',
            passwordConfirmationLabel: 'Підтвердження пароля',
            submit: 'Створити акаунт',
            haveAccount: 'Вже є акаунт?',
            signInLink: 'Увійти',
            passwordMismatch: 'Паролі не співпадають.',
            errorFallback: 'Не вдалося зареєструватися. Спробуйте ще раз.',
        },
        login: {
            title: 'Вхід',
            emailLabel: 'Email',
            passwordLabel: 'Пароль',
            forgotPassword: 'Забули пароль?',
            submit: 'Увійти',
            noAccount: 'Немає акаунта?',
            registerLink: 'Зареєструватися',
            errorFallback: 'Не вдалося увійти. Спробуйте ще раз.',
            otpRequired: 'Потрібен одноразовий код. Введіть код із застосунку автентифікації.',
            otpLabel: 'Код підтвердження',
            otpPlaceholder: 'Наприклад, 123456',
            otpHelp: 'Використайте застосунок автентифікації, щоб отримати шестизначний код.',
        },
    },
    wishlist: {
        title: 'Обране',
        clear: 'Очистити',
        loading: 'Оновлюємо список бажаного...',
        errorTitle: 'Не вдалося оновити список',
        empty: 'Поки що порожньо.',
        removeAria: ({ name }: { name: string }) => `Прибрати «${name}» зі списку бажаного`,
        noImage: 'без фото',
    },
    product: {
        reviewForm: {
            ariaLabel: 'Форма відгуку',
            title: 'Залишити відгук',
            authPromptPrefix: 'Щоб поділитися враженнями,',
            authPromptLogin: 'увійдіть',
            authPromptMiddle: 'або',
            authPromptRegister: 'зареєструйтеся',
            authPromptSuffix: '.',
            formErrorUnauthenticated: 'Щоб залишити відгук, будь ласка, увійдіть у свій акаунт.',
            successTitle: 'Дякуємо за відгук!',
            successDescription: 'Ваш відгук буде опубліковано після модерації.',
            errorFallback: 'Не вдалося надіслати відгук. Спробуйте пізніше.',
            errorTitle: 'Не вдалося надіслати відгук',
            ratingLabel: 'Оцінка',
            commentLabel: 'Коментар (необовʼязково)',
            commentPlaceholder: 'Поділіться своїми враженнями про товар',
            submitting: 'Надсилання…',
            submit: 'Надіслати відгук',
        },
    },
    notify: {
        cart: {
            add: {
                success: 'Додано до кошика',
                action: 'Відкрити кошик',
                outOfStock: 'Недостатньо на складі',
                error: 'Не вдалося додати до кошика',
            },
            update: {
                success: 'Кількість оновлено',
                outOfStock: 'Недостатньо на складі',
                error: 'Помилка оновлення кошика',
            },
            remove: {
                success: 'Видалено з кошика',
            },
        },
    },
} as const;

export default messages;
