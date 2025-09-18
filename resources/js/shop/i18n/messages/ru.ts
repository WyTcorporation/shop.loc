const messages = {
    languageName: 'Русский',
    common: {
        brand: '3D-Print Shop',
        loading: 'Загрузка…',
        actions: {
            back: 'Назад',
            retry: 'Повторить',
        },
    },
    header: {
        brand: '3D-Print Shop',
        nav: {
            catalog: 'Каталог',
            cookies: 'Настроить cookies',
        },
        account: {
            defaultName: 'Мой профиль',
            profile: 'Мой профиль',
            logout: 'Выйти',
            login: 'Войти',
            register: 'Зарегистрироваться',
        },
    },
    search: {
        placeholder: 'Поиск товаров…',
        panel: {
            minQuery: ({ min }: { min: number }) => `Введите минимум ${min} символов для поиска.`,
            loadError: 'Не удалось загрузить подсказки',
            showAll: ({ query }: { query: string }) => `Показать все результаты для “${query}”`,
            empty: 'Ничего не найдено',
        },
    },
    miniCart: {
        summary: {
            total: 'Итого',
        },
        actions: {
            viewCart: 'Открыть корзину',
            checkout: 'Оформить',
        },
        empty: 'Корзина пуста',
    },
    cart: {
        seoTitle: ({ brand }: { brand: string }) => `Корзина — ${brand}`,
        title: 'Корзина',
        loading: 'Загрузка…',
        empty: {
            message: 'Корзина пуста.',
            cta: 'Перейти к покупкам',
        },
        vendor: {
            label: 'Продавец',
            contact: 'Написать продавцу',
        },
        line: {
            remove: 'Удалить',
        },
        summary: {
            totalLabel: 'Итого',
            total: 'К оплате',
            checkout: 'Оформить',
        },
    },
    checkout: {
        seoTitle: ({ brand }: { brand: string }) => `Оформление заказа — ${brand}`,
        title: 'Оформление заказа',
        steps: {
            address: 'Адрес',
            delivery: 'Доставка',
            payment: 'Оплата',
        },
        notifications: {
            cartUnavailable: 'Корзина пуста или уже оформлена.',
            cartCheckFailed: 'Не удалось проверить корзину.',
            addressesLoadFailed: 'Не удалось загрузить адреса.',
            couponApplyFailed: 'Не удалось применить купон.',
            couponApplied: 'Купон применён.',
            couponRemoved: 'Купон отменён.',
            orderCreateSuccess: 'Заказ создан. Завершите оплату.',
            orderCreateFailed: 'Не удалось создать заказ.',
        },
        address: {
            emailLabel: 'Контактный email',
            emailPlaceholder: 'you@example.com',
            saved: {
                title: 'Сохранённые адреса',
                emptyAuthenticated: 'У вас ещё нет сохранённых адресов.',
                emptyGuest: 'Войдите, чтобы использовать сохранённые адреса.',
            },
            fields: {
                name: {
                    label: 'Имя получателя',
                    placeholder: 'Имя Фамилия',
                },
                city: {
                    label: 'Город',
                    placeholder: 'Киев',
                },
                addr: {
                    label: 'Адрес доставки',
                    placeholder: 'ул. Шевченко, 1',
                },
                postal: {
                    optionalLabel: 'Почтовый индекс (необязательно)',
                    placeholder: '01001',
                },
                phone: {
                    optionalLabel: 'Телефон (необязательно)',
                    placeholder: '+380 00 000 0000',
                },
            },
            next: 'Перейти к доставке',
        },
        billing: {
            toggle: 'Нужны данные для счёта',
            description: 'Укажите платёжные данные для счёта и документов.',
            copyFromShipping: 'Заполнить как для доставки',
            fields: {
                name: {
                    label: 'Имя / ответственное лицо',
                    placeholder: 'Имя Фамилия',
                },
                company: {
                    label: 'Компания (необязательно)',
                    placeholder: 'ООО «Пример»',
                },
                taxId: {
                    label: 'Налоговый номер (ИНН / VAT)',
                    placeholder: '1234567890',
                },
                city: {
                    label: 'Город',
                    placeholder: 'Киев',
                },
                addr: {
                    label: 'Адрес плательщика',
                    placeholder: 'ул. Шевченко, 1',
                },
                postal: {
                    optionalLabel: 'Почтовый индекс (необязательно)',
                    placeholder: '01001',
                },
            },
        },
        errors: {
            emailRequired: 'Укажите email для подтверждения.',
            emailInvalid: 'Укажите корректный email.',
            shippingNameRequired: 'Укажите имя получателя.',
            shippingCityRequired: 'Укажите город доставки.',
            shippingAddrRequired: 'Укажите адрес доставки.',
            billingNameRequired: 'Укажите имя плательщика.',
            billingCityRequired: 'Укажите город плательщика.',
            billingAddrRequired: 'Укажите адрес плательщика.',
            billingTaxRequired: 'Укажите налоговый номер компании.',
        },
        delivery: {
            title: 'Способ доставки',
            commentLabel: 'Комментарий курьеру (необязательно)',
            commentPlaceholder: 'Например, позвоните за 30 минут до доставки',
            options: {
                nova: {
                    title: 'Новая почта',
                    description: 'Доставка по Украине за 2–3 дня.',
                },
                ukr: {
                    title: 'Укрпочта',
                    description: 'Экономная доставка 3–5 дней до отделения.',
                },
                pickup: {
                    title: 'Самовывоз',
                    description: 'Заберите заказ сегодня в нашей мастерской (Киев).',
                },
            },
        },
        coupon: {
            title: 'Купон',
            placeholder: 'Введите код купона',
            applying: 'Применение…',
            apply: 'Применить',
            applied: ({ code }: { code: string }) => `Купон применён: ${code}`,
        },
        summary: {
            title: 'Ваш заказ',
            quantity: ({ count }: { count: number }) => `Количество: ${count}`,
            subtotal: 'Сумма товаров',
            discount: 'Скидка',
            total: 'К оплате',
            notice: 'После перехода к оплате изменить адрес или доставку можно будет только создав новый заказ.',
            goToPayment: 'Перейти к оплате',
            creating: 'Создание…',
        },
        payment: {
            preparing: 'Подготовка оплаты…',
            orderNumberLabel: 'Номер заказа',
            confirmationNotice: ({ email }: { email: string }) => `Подтверждение будет отправлено на ${email}.`,
            totalNotice: ({ amount }: { amount: string }) => `К оплате: ${amount}`,
            title: 'Оплата',
            description: 'Безопасная оплата через Stripe. После успешной транзакции вы будете перенаправлены к подтверждению заказа.',
            billingTitle: 'Платёжные данные',
            billingTax: ({ taxId }: { taxId: string }) => `Налоговый номер: ${taxId}`,
            billingMatchesShipping: 'Платёжные данные совпадают с адресом доставки.',
            shippingTitle: 'Доставка',
            shippingMethod: ({ method }: { method: string }) => `Способ доставки: ${method}`,
            shippingComment: ({ comment }: { comment: string }) => `Комментарий: ${comment}`,
            itemsTitle: 'Товары',
        },
        notes: {
            delivery: ({ method }: { method: string }) => `Доставка: ${method}`,
            comment: ({ comment }: { comment: string }) => `Комментарий: ${comment}`,
        },
    },
} as const;

export default messages;
