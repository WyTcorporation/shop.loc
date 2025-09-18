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
} as const;

export default messages;
