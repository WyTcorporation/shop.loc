const messages = {
    languageName: 'Русский',
    common: {
        brand: '3D-Print Shop',
        loading: 'Загрузка…',
        actions: {
            back: 'Назад',
            retry: 'Повторить',
        },
        notFound: {
            seoTitle: ({ brand }: { brand: string }) => `Страница не найдена — 404 — ${brand}`,
            seoDescription: 'Страница не найдена',
            title: '404 — Страница не найдена',
            description: 'Возможно, ссылка устарела или была удалена.',
            action: 'Вернуться в каталог',
        },
        errorBoundary: {
            title: 'Что-то пошло не так',
            descriptionFallback: 'Непредвиденная ошибка.',
            reload: 'Перезагрузить',
            home: 'На главную',
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
    consent: {
        ariaLabel: 'Настройки cookies',
        message: 'Мы используем cookies для аналитики (GA4). Нажмите «Разрешить», чтобы включить их. Вы можете изменить выбор в любой момент.',
        decline: 'Отклонить',
        accept: 'Разрешить',
        note: 'Обязательные cookies не отслеживают вас. Аналитика включается только с вашего согласия.',
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
    recentlyViewed: {
        title: 'Вы недавно смотрели',
        empty: 'Вы ещё не просмотрели ни одного товара.',
        noImage: 'без фото',
    },
    orderChat: {
        title: 'Чат с продавцом',
        orderLabel: ({ number }: { number: string | number }) => `Заказ ${number}`,
        actions: {
            refresh: 'Обновить',
            send: 'Отправить',
            sending: 'Отправка…',
        },
        loading: 'Загрузка сообщений…',
        empty: 'Сообщений пока нет. Напишите первым!',
        you: 'Вы',
        seller: 'Продавец',
        inputPlaceholder: 'Ваше сообщение продавцу…',
        inputHint: {
            maxLength: ({ limit }: { limit: number }) => `До ${limit} символов`,
        },
        guestPrompt: {
            prefix: 'Чтобы написать продавцу,',
            login: 'войдите',
            or: 'или',
            register: 'зарегистрируйтесь',
            suffix: '.',
        },
        errors: {
            load: 'Не удалось загрузить сообщения.',
            send: 'Не удалось отправить сообщение.',
        },
    },
    catalog: {
        seo: {
            listName: ({ category }: { category?: string }) => category ? `Каталог — ${category}` : 'Каталог',
            documentTitle: ({ category, query }: { category?: string; query?: string }) => {
                const parts = ['Каталог'];
                if (category) parts.push(category);
                if (query) parts.push(`поиск “${query}”`);
                return parts.join(' — ');
            },
            pageTitle: ({ category, query, brand }: { category?: string; query?: string; brand: string }) => {
                const parts = ['Каталог'];
                if (category) parts.push(category);
                if (query) parts.push(`поиск “${query}”`);
                return `${parts.join(' — ')} — ${brand}`;
            },
            description: ({ category, query }: { category?: string; query?: string }) => [
                'Каталог интернет-магазина. Фильтры: категория, цвет, размер, цена.',
                category ? `Категория: ${category}.` : '',
                query ? `Поиск: ${query}.` : '',
            ].filter(Boolean).join(' '),
            breadcrumbHome: 'Главная',
            breadcrumbCatalog: 'Каталог',
        },
        header: {
            title: 'Каталог',
            categoryPlaceholder: 'Категория',
            allCategories: 'Все категории',
            sort: {
                new: 'Новинки',
                priceAsc: 'Цена ↑',
                priceDesc: 'Цена ↓',
            },
        },
        filters: {
            searchPlaceholder: 'Поиск товаров…',
            priceMinPlaceholder: 'Цена от',
            priceMaxPlaceholder: 'до',
            applyPrice: 'Применить',
            clearAll: 'Сбросить всё',
            active: {
                color: ({ value }: { value: string }) => `Цвет: ${value}`,
                size: ({ value }: { value: string }) => `Размер: ${value}`,
                minPrice: ({ value }: { value: number }) => `От: ${value}`,
                maxPrice: ({ value }: { value: number }) => `До: ${value}`,
                clearTooltip: 'Сбросить этот фильтр',
                clearAll: 'Сбросить всё',
            },
            facets: {
                categories: 'Категории',
                colors: 'Цвет',
                sizes: 'Размер',
                empty: 'нет данных',
            },
        },
        products: {
            empty: 'Ничего не найдено. Попробуйте изменить фильтры.',
        },
        cards: {
            noImage: 'без фото',
            outOfStock: 'Нет в наличии',
            adding: 'Покупаем…',
            addToCart: 'Купить',
        },
        pagination: {
            prev: 'Назад',
            next: 'Далее',
            pageStatus: ({ page, lastPage }: { page: number; lastPage: number }) => `Страница ${page} из ${lastPage}`,
        },
    },
    sellerPage: {
        pageTitle: ({ name }: { name?: string }) => name ? `${name} — Продавец` : 'Продавец',
        documentTitle: ({ name, brand }: { name?: string; brand: string }) =>
            name ? `${name} — Продавец — ${brand}` : `Продавец — ${brand}`,
        productsTitle: 'Товары продавца',
        loadingVendor: 'Загрузка информации о продавце…',
        notFound: 'Продавец не найден.',
        noProducts: 'У этого продавца пока нет доступных товаров.',
        noImage: 'без фото',
        contact: {
            email: ({ email }: { email: string }) => `Email: ${email}`,
            phone: ({ phone }: { phone: string }) => `Телефон: ${phone}`,
        },
        seo: {
            title: ({ name, brand }: { name?: string; brand: string }) =>
                name ? `${name} — Продавец — ${brand}` : `Продавец — ${brand}`,
            description: ({ description, email, phone }: { description?: string; email?: string; phone?: string }) => {
                const parts = [
                    description?.trim() ?? '',
                    email ? `Email: ${email}` : '',
                    phone ? `Телефон: ${phone}` : '',
                ].filter(Boolean);
                return parts.length ? parts.join(' ') : 'Страница продавца. Контакты и товары.';
            },
        },
        pagination: {
            prev: 'Назад',
            next: 'Далее',
            status: ({ page, lastPage }: { page: number; lastPage: number }) => `Страница ${page} из ${lastPage}`,
        },
        errors: {
            loadProducts: 'Не удалось загрузить товары продавца.',
        },
        ga: {
            listName: ({ name }: { name: string }) => `Продавец ${name}`,
        },
    },
    profile: {
        navigation: {
            overview: 'Профиль',
            orders: 'Мои заказы',
            addresses: 'Сохранённые адреса',
            points: 'Бонусные баллы',
        },
        overview: {
            loading: 'Загрузка профиля…',
            title: 'Профиль',
            welcome: ({ name }: { name: string }) =>
                `Добро пожаловать, ${name}. Управляйте своими данными и переходите к другим разделам профиля.`,
            guestName: 'пользователь',
            personalDataTitle: 'Личные данные',
            verification: {
                title: 'Электронная почта не подтверждена.',
                description: 'Проверьте почтовый ящик или отправьте письмо с подтверждением повторно.',
                resend: {
                    sending: 'Отправка…',
                    action: 'Отправить письмо ещё раз',
                },
                successFallback: 'Письмо для подтверждения отправлено повторно.',
                errorFallback: 'Не удалось отправить письмо подтверждения. Попробуйте ещё раз.',
            },
            form: {
                labels: {
                    name: 'Имя',
                    email: 'Email',
                    newPassword: 'Новый пароль',
                    confirmPassword: 'Подтверждение пароля',
                },
                placeholders: {
                    name: 'Введите имя',
                    email: 'your@email.com',
                    newPassword: 'Оставьте пустым, чтобы не менять',
                    confirmPassword: 'Повторите новый пароль',
                },
                hintPasswordOptional:
                    'Пароль можно не заполнять, если вы не планируете его менять. Email должен быть уникальным.',
                hintApplyImmediately: 'Изменения вступают в силу сразу после сохранения.',
                submit: {
                    saving: 'Сохранение…',
                    save: 'Сохранить изменения',
                },
            },
            info: {
                id: 'ID',
                name: 'Имя',
                email: 'Email',
                verified: 'Email подтверждён',
                verifiedYes: 'Да',
                verifiedNo: 'Нет',
            },
            session: {
                tokenNote: 'Токен Sanctum сохраняется локально для авторизованных запросов к API.',
                logout: {
                    processing: 'Выход…',
                    action: 'Выйти',
                    error: 'Не удалось выйти. Попробуйте ещё раз.',
                },
            },
            notifications: {
                updateSuccess: 'Данные профиля обновлены.',
            },
            errors: {
                update: 'Не удалось обновить профиль. Попробуйте ещё раз.',
                loadTwoFactorStatus: 'Не удалось загрузить статус двухфакторной аутентификации.',
                startTwoFactor: 'Не удалось начать настройку двухфакторной аутентификации.',
                confirmTwoFactor: 'Не удалось подтвердить код. Попробуйте ещё раз.',
                disableTwoFactor: 'Не удалось отключить двухфакторную аутентификацию.',
                resendVerification: 'Не удалось отправить письмо подтверждения. Попробуйте ещё раз.',
            },
            twoFactor: {
                title: 'Двухфакторная аутентификация',
                statusLabel: 'Статус:',
                status: {
                    enabled: 'Включена',
                    pending: 'Ожидает подтверждения',
                    disabled: 'Отключена',
                },
                confirmedAtLabel: 'Подтверждена:',
                description:
                    'Двухфакторная аутентификация добавляет дополнительный уровень безопасности для вашей учётной записи.',
                loadingStatus: 'Загрузка статуса…',
                secret: {
                    title: 'Секретный ключ',
                    instructions:
                        'Добавьте этот ключ в приложение аутентификации (Google Authenticator, 1Password, Authy и т. д.). Вы также можете открыть настройку по ссылке ниже.',
                    openApp: 'Открыть в приложении',
                },
                confirm: {
                    codeLabel: 'Код подтверждения',
                    codePlaceholder: 'Введите код из приложения',
                    helper: 'Введите шестизначный код из приложения аутентификации, чтобы завершить настройку.',
                    submit: 'Подтвердить',
                    submitting: 'Подтверждение…',
                    cancel: 'Отменить',
                },
                callouts: {
                    pendingSetup:
                        'Предыдущая настройка не завершена. Вы можете сгенерировать новый секретный ключ, чтобы начать заново.',
                },
                enable: {
                    action: 'Включить 2FA',
                    loading: 'Подождите…',
                },
                disable: {
                    action: 'Отключить 2FA',
                    confirm: 'Вы уверены, что хотите отключить двухфакторную аутентификацию?',
                },
                messages: {
                    enabled: 'Двухфакторная аутентификация включена.',
                    disabled: 'Двухфакторная аутентификация отключена.',
                    emptyCode: 'Введите код подтверждения из приложения.',
                },
            },
        },
        orders: {
            loading: 'Загрузка заказов…',
            title: 'Мои заказы',
            description: 'Просматривайте историю покупок, статусы заказов и переходите к подробностям.',
            error: 'Не удалось загрузить заказы.',
            table: {
                loading: 'Загрузка…',
                empty: {
                    description: 'Вы ещё не сделали ни одного заказа.',
                    cta: 'Перейти в каталог',
                },
                headers: {
                    number: 'Номер',
                    date: 'Дата',
                    status: 'Статус',
                    total: 'Сумма',
                    actions: 'Действия',
                },
                view: 'Подробнее о заказе',
            },
        },
        addresses: {
            loading: 'Загрузка адресов…',
            title: 'Сохранённые адреса',
            description: 'Управляйте адресами доставки, чтобы быстрее оформлять новые заказы.',
            error: 'Не удалось загрузить адреса.',
            list: {
                loading: 'Загрузка…',
                empty: 'У вас ещё нет сохранённых адресов. Добавьте адрес при оформлении заказа.',
                defaultName: 'Без названия',
                fields: {
                    city: 'Город',
                    address: 'Адрес',
                    postalCode: 'Почтовый индекс',
                    phone: 'Телефон',
                },
            },
        },
        points: {
            loading: 'Загрузка баллов…',
            title: 'Бонусные баллы',
            description: 'Отслеживайте доступный баланс и историю использования бонусных баллов.',
            error: 'Не удалось загрузить информацию о баллах.',
            type: {
                default: 'Операция',
                earn: 'Начисление',
                redeem: 'Списание',
            },
            stats: {
                balance: 'Доступно',
                earned: 'Начислено',
                spent: 'Использовано',
            },
            table: {
                loading: 'Загрузка…',
                empty:
                    'История баллов пуста. Используйте баллы при оформлении заказов, чтобы видеть движение средств.',
                headers: {
                    date: 'Дата',
                    description: 'Описание',
                    type: 'Тип',
                    amount: 'Количество',
                },
                type: {
                    default: 'Операция',
                    earn: 'Начисление',
                    redeem: 'Списание',
                },
            },
        },
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
    order: {
        confirmation: {
            loading: 'Загрузка…',
            notFound: 'Заказ не найден.',
            seoTitle: ({ number, brand }: { number: string; brand: string }) => `Заказ ${number} — ${brand}`,
            title: ({ number }: { number: string }) => `Спасибо! Заказ ${number} оформлен`,
            confirmationNotice: ({ email }: { email: string }) => `Подтверждение отправлено на ${email}.`,
            paymentPending: 'Ожидается оплата.',
            chat: {
                open: 'Написать продавцу',
                close: 'Скрыть чат',
            },
            shipping: {
                title: 'Доставка и отслеживание',
                trackingNumber: 'Номер отслеживания:',
                pending: 'Ожидается',
            },
            billing: {
                title: 'Платежные данные',
                taxIdLabel: 'Налоговый номер:',
            },
            table: {
                product: 'Товар',
                quantity: 'Кол-во',
                price: 'Цена',
                total: 'Сумма',
                viewProduct: 'Просмотреть товар',
                vendor: 'Продавец:',
                contactSeller: 'Написать продавцу',
                subtotal: 'Итого за товары',
                coupon: 'Купон',
                discount: 'Скидка',
                loyalty: 'Использованные баллы',
                loyaltyValue: ({ amount }: { amount: string }) => `(−${amount})`,
                amountDue: 'К оплате',
            },
            cta: {
                continue: 'Продолжить покупки',
            },
            payment: {
                title: 'Оплата заказа',
                description: 'Безопасно через Stripe. Доступны карты и локальные методы (ЕС).',
            },
        },
    },
    auth: {
        shared: {
            loading: 'Загрузка…',
            processing: 'Подождите…',
        },
        register: {
            title: 'Регистрация',
            nameLabel: 'Имя',
            emailLabel: 'Email',
            passwordLabel: 'Пароль',
            passwordConfirmationLabel: 'Подтверждение пароля',
            submit: 'Создать аккаунт',
            haveAccount: 'Уже есть аккаунт?',
            signInLink: 'Войти',
            passwordMismatch: 'Пароли не совпадают.',
            errorFallback: 'Не удалось зарегистрироваться. Попробуйте ещё раз.',
        },
        login: {
            title: 'Вход',
            emailLabel: 'Email',
            passwordLabel: 'Пароль',
            forgotPassword: 'Забыли пароль?',
            submit: 'Войти',
            noAccount: 'Нет аккаунта?',
            registerLink: 'Зарегистрироваться',
            errorFallback: 'Не удалось войти. Попробуйте ещё раз.',
            otpRequired: 'Требуется одноразовый код. Введите код из приложения аутентификации.',
            otpLabel: 'Код подтверждения',
            otpPlaceholder: 'Например, 123456',
            otpHelp: 'Используйте приложение аутентификации, чтобы получить шестизначный код.',
        },
        reset: {
            fields: {
                emailLabel: 'Email',
                passwordLabel: 'Новый пароль',
                passwordConfirmationLabel: 'Подтверждение пароля',
            },
            errors: {
                emailRequired: 'Укажите email.',
                emailInvalid: 'Укажите корректный адрес электронной почты.',
                passwordRequired: 'Укажите новый пароль.',
                passwordTooShort: 'Пароль должен содержать минимум 8 символов.',
                confirmationRequired: 'Подтвердите новый пароль.',
                passwordMismatch: 'Пароли не совпадают.',
            },
            shared: {
                backToLogin: 'Вернуться ко входу',
            },
            request: {
                title: 'Восстановление пароля',
                description: 'Введите email, и мы отправим ссылку для восстановления пароля.',
                submit: 'Отправить ссылку',
                submitting: 'Отправляем…',
                remember: 'Помните пароль?',
                successFallback: 'Ссылка для восстановления пароля отправлена.',
                errorFallback: 'Не удалось отправить письмо. Попробуйте ещё раз.',
            },
            update: {
                title: 'Сброс пароля',
                description: 'Введите данные, чтобы установить новый пароль для аккаунта.',
                submit: 'Изменить пароль',
                submitting: 'Сохраняем…',
                successFallback: 'Пароль успешно изменён. Теперь можно войти.',
                errorFallback: 'Не удалось изменить пароль. Проверьте данные и попробуйте ещё раз.',
                backToLoginPrefix: 'Вернуться на',
                backToLoginLink: 'страницу входа',
                backToLoginSuffix: '.',
            },
        },
    },
    wishlist: {
        badge: 'Избранное',
        title: 'Избранное',
        clear: 'Очистить',
        loading: 'Обновляем список желаний...',
        errorTitle: 'Не удалось обновить список',
        empty: 'Пока пусто.',
        button: {
            add: 'Добавить в избранное',
            remove: 'В избранном',
            addAria: 'Добавить в избранное',
            removeAria: 'Убрать из избранного',
        },
        removeAria: ({ name }: { name: string }) => `Убрать «${name}» из списка желаний`,
        noImage: 'без фото',
    },
    product: {
        seo: {
            pageTitle: ({ name, price, brand }: { name: string; price: string; brand: string }) => `${name} — ${price} — ${brand}`,
            fallbackTitle: ({ brand }: { brand: string }) => `Товар — ${brand}`,
            description: ({ name, price, inStock }: { name: string; price: string; inStock: boolean }) =>
                `Купить ${name} за ${price}. ${inStock ? 'В наличии.' : 'Нет в наличии.'} Заказывайте онлайн.`,
            fallbackDescription: 'Карточка товара в магазине.',
            breadcrumbHome: 'Главная',
            breadcrumbCatalog: 'Каталог',
        },
        gallery: {
            noImage: 'без фото',
            openImage: ({ index }: { index: number }) => `Открыть изображение ${index}`,
        },
        reviews: {
            ariaLabel: 'Отзывы',
            title: 'Отзывы',
            summary: {
                loading: 'Загрузка рейтинга…',
                label: 'Средний рейтинг:',
                of: ({ max }: { max: number }) => `из ${max}`,
                empty: 'Еще нет отзывов',
            },
            loading: 'Загрузка отзывов…',
            empty: 'Отзывов пока нет. Станьте первым!',
            anonymous: 'Пользователь',
            ratingLabel: ({ value, max }: { value: number; max: number }) => `${value} из ${max}`,
            pending: 'Отзыв ожидает модерации. Мы сообщим после публикации.',
        },
        stock: {
            available: ({ count }: { count: number }) => `В наличии: ${count} шт.`,
            unavailable: 'Нет в наличии',
        },
        actions: {
            addToCart: 'Добавить в корзину',
            backToCatalog: '← К каталогу',
        },
        toasts: {
            added: {
                title: 'Добавлено в корзину',
                action: 'Открыть корзину',
                error: 'Не удалось добавить в корзину',
            },
        },
        tabs: {
            description: 'Описание',
            specs: 'Характеристики',
            delivery: 'Доставка',
        },
        description: {
            empty: 'Описание пока отсутствует.',
        },
        specs: {
            empty: 'Характеристики еще не добавлены.',
        },
        delivery: {
            items: {
                novaPoshta: 'Новая Почта по Украине — 1–3 дня.',
                courier: 'Курьер в крупных городах — 1–2 дня.',
                payment: 'Оплата: картой онлайн или наложенный платеж.',
                returns: 'Возврат/обмен — 14 дней (согласно закону о защите прав потребителей).',
            },
        },
        ratingStars: {
            option: ({ value, max }: { value: number; max: number }) => `Оценка ${value} из ${max}`,
            hint: ({ value, max }: { value: number; max: number }) => `${value} из ${max}`,
        },
        similar: {
            count: ({ count }: { count: number }) => `Найдено похожих: ${count}`,
        },
        reviewForm: {
            ariaLabel: 'Форма отзыва',
            title: 'Оставить отзыв',
            authPromptPrefix: 'Чтобы поделиться впечатлениями,',
            authPromptLogin: 'войдите',
            authPromptMiddle: 'или',
            authPromptRegister: 'зарегистрируйтесь',
            authPromptSuffix: '.',
            formErrorUnauthenticated: 'Чтобы оставить отзыв, войдите в свой аккаунт.',
            successTitle: 'Спасибо за отзыв!',
            successDescription: 'Ваш отзыв будет опубликован после модерации.',
            errorFallback: 'Не удалось отправить отзыв. Попробуйте позже.',
            errorTitle: 'Не удалось отправить отзыв',
            ratingLabel: 'Оценка',
            commentLabel: 'Комментарий (необязательно)',
            commentPlaceholder: 'Поделитесь впечатлениями о товаре',
            submitting: 'Отправка…',
            submit: 'Отправить отзыв',
        },
    },
    notify: {
        cart: {
            add: {
                success: 'Добавлено в корзину',
                action: 'Открыть корзину',
                outOfStock: 'Недостаточно на складе',
                error: 'Не удалось добавить в корзину',
            },
            update: {
                success: 'Количество обновлено',
                outOfStock: 'Недостаточно на складе',
                error: 'Не удалось обновить корзину',
            },
            remove: {
                success: 'Удалено из корзины',
            },
        },
    },
} as const;

export default messages;
