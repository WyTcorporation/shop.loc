const messages = {
    languageName: 'Українська',
    common: {
        brand: '3D-Print Shop',
        loading: 'Завантаження…',
        actions: {
            back: 'Назад',
            retry: 'Повторити',
        },
        notFound: {
            seoTitle: ({ brand }: { brand: string }) => `Сторінку не знайдено — 404 — ${brand}`,
            seoDescription: 'Сторінку не знайдено',
            title: '404 — Сторінку не знайдено',
            description: 'Можливо, посилання застаріло або було видалено.',
            action: 'Повернутися до каталогу',
        },
        errorBoundary: {
            title: 'Щось пішло не так',
            descriptionFallback: 'Несподівана помилка.',
            reload: 'Перезавантажити',
            home: 'На головну',
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
    consent: {
        ariaLabel: 'Налаштування cookies',
        message: 'Ми використовуємо cookies для аналітики (GA4). Натисніть «Дозволити», щоб увімкнути. Ви можете змінити вибір будь-коли.',
        decline: 'Відхилити',
        accept: 'Дозволити',
        note: 'Обов’язкові cookies не відслідковують вас. Аналітика вмикається лише за згодою.',
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
    recentlyViewed: {
        title: 'Ви нещодавно переглядали',
        empty: 'Ще не переглядали жодного товару.',
        noImage: 'без фото',
    },
    orderChat: {
        title: 'Чат з продавцем',
        orderLabel: ({ number }: { number: string | number }) => `Замовлення ${number}`,
        actions: {
            refresh: 'Оновити',
            send: 'Надіслати',
            sending: 'Надсилання…',
        },
        loading: 'Завантаження повідомлень…',
        empty: 'Повідомлень ще немає. Напишіть першим!',
        you: 'Ви',
        seller: 'Продавець',
        inputPlaceholder: 'Ваше повідомлення продавцю…',
        inputHint: {
            maxLength: ({ limit }: { limit: number }) => `До ${limit} символів`,
        },
        guestPrompt: {
            prefix: 'Щоб написати продавцю,',
            login: 'увійдіть',
            or: 'або',
            register: 'зареєструйтесь',
            suffix: '.',
        },
        errors: {
            load: 'Не вдалося завантажити повідомлення.',
            send: 'Не вдалося надіслати повідомлення.',
        },
    },
    catalog: {
        seo: {
            listName: ({ category }: { category?: string }) => category ? `Каталог — ${category}` : 'Каталог',
            documentTitle: ({ category, query }: { category?: string; query?: string }) => {
                const parts = ['Каталог'];
                if (category) parts.push(category);
                if (query) parts.push(`пошук “${query}”`);
                return parts.join(' — ');
            },
            pageTitle: ({ category, query, brand }: { category?: string; query?: string; brand: string }) => {
                const parts = ['Каталог'];
                if (category) parts.push(category);
                if (query) parts.push(`пошук “${query}”`);
                return `${parts.join(' — ')} — ${brand}`;
            },
            description: ({ category, query }: { category?: string; query?: string }) => [
                'Каталог інтернет-магазину. Фільтри: категорія, колір, розмір, ціна.',
                category ? `Категорія: ${category}.` : '',
                query ? `Пошук: ${query}.` : '',
            ].filter(Boolean).join(' '),
            breadcrumbHome: 'Головна',
            breadcrumbCatalog: 'Каталог',
        },
        header: {
            title: 'Каталог',
            categoryPlaceholder: 'Категорія',
            allCategories: 'Всі категорії',
            sort: {
                new: 'Новинки',
                priceAsc: 'Ціна ↑',
                priceDesc: 'Ціна ↓',
            },
        },
        filters: {
            searchPlaceholder: 'Пошук товарів…',
            priceMinPlaceholder: 'Ціна від',
            priceMaxPlaceholder: 'до',
            applyPrice: 'Застосувати',
            clearAll: 'Скинути все',
            active: {
                color: ({ value }: { value: string }) => `Колір: ${value}`,
                size: ({ value }: { value: string }) => `Розмір: ${value}`,
                minPrice: ({ value }: { value: number }) => `Від: ${value}`,
                maxPrice: ({ value }: { value: number }) => `До: ${value}`,
                clearTooltip: 'Скинути цей фільтр',
                clearAll: 'Скинути все',
            },
            facets: {
                categories: 'Категорії',
                colors: 'Колір',
                sizes: 'Розмір',
                empty: 'нема даних',
            },
        },
        products: {
            empty: 'Нічого не знайдено. Спробуйте змінити фільтри.',
        },
        cards: {
            noImage: 'без фото',
            outOfStock: 'Немає в наявності',
            adding: 'Купуємо…',
            addToCart: 'Купити',
        },
        pagination: {
            prev: 'Назад',
            next: 'Далі',
            pageStatus: ({ page, lastPage }: { page: number; lastPage: number }) => `Сторінка ${page} із ${lastPage}`,
        },
    },
    sellerPage: {
        pageTitle: ({ name }: { name?: string }) => name ? `${name} — Продавець` : 'Продавець',
        documentTitle: ({ name, brand }: { name?: string; brand: string }) =>
            name ? `${name} — Продавець — ${brand}` : `Продавець — ${brand}`,
        productsTitle: 'Товари продавця',
        loadingVendor: 'Завантаження інформації про продавця…',
        notFound: 'Продавця не знайдено.',
        noProducts: 'У цього продавця поки немає доступних товарів.',
        noImage: 'без фото',
        contact: {
            email: ({ email }: { email: string }) => `Email: ${email}`,
            phone: ({ phone }: { phone: string }) => `Телефон: ${phone}`,
        },
        seo: {
            title: ({ name, brand }: { name?: string; brand: string }) =>
                name ? `${name} — Продавець — ${brand}` : `Продавець — ${brand}`,
            description: ({ description, email, phone }: { description?: string; email?: string; phone?: string }) => {
                const parts = [
                    description?.trim() ?? '',
                    email ? `Email: ${email}` : '',
                    phone ? `Телефон: ${phone}` : '',
                ].filter(Boolean);
                return parts.length ? parts.join(' ') : 'Сторінка продавця. Контакти та товари.';
            },
        },
        pagination: {
            prev: 'Назад',
            next: 'Далі',
            status: ({ page, lastPage }: { page: number; lastPage: number }) => `Сторінка ${page} з ${lastPage}`,
        },
        errors: {
            loadProducts: 'Не вдалося завантажити товари продавця.',
        },
        ga: {
            listName: ({ name }: { name: string }) => `Продавець ${name}`,
        },
    },
    profile: {
        navigation: {
            overview: 'Профіль',
            orders: 'Мої замовлення',
            addresses: 'Збережені адреси',
            points: 'Бонусні бали',
        },
        overview: {
            loading: 'Завантаження профілю…',
            title: 'Профіль',
            welcome: ({ name }: { name: string }) =>
                `Ласкаво просимо, ${name}. Керуйте своїми даними та переходьте до інших розділів профілю.`,
            guestName: 'користувачу',
            personalDataTitle: 'Особисті дані',
            verification: {
                title: 'Електронну пошту не підтверджено.',
                description: 'Перевірте поштову скриньку або надішліть лист із підтвердженням повторно.',
                resend: {
                    sending: 'Надсилання…',
                    action: 'Надіслати лист повторно',
                },
                successFallback: 'Лист для підтвердження повторно надіслано.',
                errorFallback: 'Не вдалося надіслати лист підтвердження. Спробуйте ще раз.',
            },
            form: {
                labels: {
                    name: 'Імʼя',
                    email: 'Email',
                    newPassword: 'Новий пароль',
                    confirmPassword: 'Підтвердження пароля',
                },
                placeholders: {
                    name: 'Введіть імʼя',
                    email: 'your@email.com',
                    newPassword: 'Залиште порожнім, щоб не змінювати',
                    confirmPassword: 'Повторіть новий пароль',
                },
                hintPasswordOptional:
                    'Пароль можна не заповнювати, якщо ви не плануєте його змінювати. Email повинен бути унікальним.',
                hintApplyImmediately: 'Зміни набувають чинності одразу після збереження.',
                submit: {
                    saving: 'Збереження…',
                    save: 'Зберегти зміни',
                },
            },
            info: {
                id: 'ID',
                name: 'Імʼя',
                email: 'Email',
                verified: 'Email підтверджено',
                verifiedYes: 'Так',
                verifiedNo: 'Ні',
            },
            session: {
                tokenNote: 'Токен Sanctum збережено локально для авторизованих запитів до API.',
                logout: {
                    processing: 'Вихід…',
                    action: 'Вийти',
                    error: 'Не вдалося вийти. Спробуйте ще раз.',
                },
            },
            notifications: {
                updateSuccess: 'Дані профілю оновлено.',
            },
            errors: {
                update: 'Не вдалося оновити профіль. Спробуйте ще раз.',
                loadTwoFactorStatus: 'Не вдалося завантажити статус двофакторної автентифікації.',
                startTwoFactor: 'Не вдалося розпочати налаштування двофакторної автентифікації.',
                confirmTwoFactor: 'Не вдалося підтвердити код. Спробуйте ще раз.',
                disableTwoFactor: 'Не вдалося вимкнути двофакторну автентифікацію.',
                resendVerification: 'Не вдалося надіслати лист підтвердження. Спробуйте ще раз.',
            },
            twoFactor: {
                title: 'Двофакторна автентифікація',
                statusLabel: 'Статус:',
                status: {
                    enabled: 'Увімкнено',
                    pending: 'Очікує підтвердження',
                    disabled: 'Вимкнено',
                },
                confirmedAtLabel: 'Підтверджено:',
                description: 'Двофакторна автентифікація додає додатковий рівень безпеки для вашого облікового запису.',
                loadingStatus: 'Завантаження статусу…',
                secret: {
                    title: 'Секретний ключ',
                    instructions:
                        'Додайте цей ключ у застосунок автентифікації (Google Authenticator, 1Password, Authy тощо). Ви також можете відкрити налаштування безпосередньо за посиланням нижче.',
                    openApp: 'Відкрити в застосунку',
                },
                confirm: {
                    codeLabel: 'Код підтвердження',
                    codePlaceholder: 'Введіть код із застосунку',
                    helper: 'Введіть шестизначний код із вашого застосунку автентифікації, щоб завершити налаштування.',
                    submit: 'Підтвердити',
                    submitting: 'Підтвердження…',
                    cancel: 'Скасувати',
                },
                callouts: {
                    pendingSetup: 'Попереднє налаштування не завершено. Ви можете згенерувати новий секретний ключ, щоб почати знову.',
                },
                enable: {
                    action: 'Увімкнути 2FA',
                    loading: 'Зачекайте…',
                },
                disable: {
                    action: 'Вимкнути 2FA',
                    confirm: 'Ви впевнені, що хочете вимкнути двофакторну автентифікацію?',
                },
                messages: {
                    enabled: 'Двофакторну автентифікацію увімкнено.',
                    disabled: 'Двофакторну автентифікацію вимкнено.',
                    emptyCode: 'Введіть код підтвердження з застосунку.',
                },
            },
        },
        orders: {
            loading: 'Завантаження замовлень…',
            title: 'Мої замовлення',
            description: 'Переглядайте історію покупок, статус замовлень та переходьте до їх деталей.',
            error: 'Не вдалося завантажити замовлення.',
            table: {
                loading: 'Завантаження…',
                empty: {
                    description: 'Ви ще не зробили жодного замовлення.',
                    cta: 'Перейти до каталогу',
                },
                headers: {
                    number: 'Номер',
                    date: 'Дата',
                    status: 'Статус',
                    total: 'Сума',
                    actions: 'Дії',
                },
                view: 'Деталі замовлення',
            },
        },
        addresses: {
            loading: 'Завантаження адрес…',
            title: 'Збережені адреси',
            description: 'Керуйте адресами доставки, щоб швидко оформлювати нові замовлення.',
            error: 'Не вдалося завантажити адреси.',
            list: {
                loading: 'Завантаження…',
                empty: 'У вас ще немає збережених адрес. Додайте адресу під час оформлення замовлення.',
                defaultName: 'Без назви',
                fields: {
                    city: 'Місто',
                    address: 'Адреса',
                    postalCode: 'Поштовий індекс',
                    phone: 'Телефон',
                },
            },
        },
        points: {
            loading: 'Завантаження балів…',
            title: 'Бонусні бали',
            description: 'Відстежуйте доступний баланс та історію використання бонусних балів.',
            error: 'Не вдалося завантажити інформацію про бали.',
            type: {
                default: 'Операція',
                earn: 'Нарахування',
                redeem: 'Списання',
            },
            stats: {
                balance: 'Доступно',
                earned: 'Нараховано',
                spent: 'Використано',
            },
            table: {
                loading: 'Завантаження…',
                empty:
                    'Історія балів порожня. Використовуйте бали під час оформлення замовлень, щоб побачити рух коштів.',
                headers: {
                    date: 'Дата',
                    description: 'Опис',
                    type: 'Тип',
                    amount: 'Кількість',
                },
                type: {
                    default: 'Операція',
                    earn: 'Нарахування',
                    redeem: 'Списання',
                },
            },
        },
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
        reset: {
            fields: {
                emailLabel: 'Email',
                passwordLabel: 'Новий пароль',
                passwordConfirmationLabel: 'Підтвердження пароля',
            },
            errors: {
                emailRequired: 'Вкажіть email.',
                emailInvalid: 'Вкажіть коректну електронну адресу.',
                passwordRequired: 'Вкажіть новий пароль.',
                passwordTooShort: 'Пароль має містити щонайменше 8 символів.',
                confirmationRequired: 'Підтвердіть новий пароль.',
                passwordMismatch: 'Паролі не співпадають.',
            },
            shared: {
                backToLogin: 'Повернутися до входу',
            },
            request: {
                title: 'Відновлення пароля',
                description: 'Введіть email, і ми надішлемо посилання для відновлення пароля.',
                submit: 'Надіслати посилання',
                submitting: 'Надсилаємо…',
                remember: 'Памʼятаєте пароль?',
                successFallback: 'Посилання для відновлення пароля надіслано.',
                errorFallback: 'Не вдалося надіслати лист. Спробуйте ще раз.',
            },
            update: {
                title: 'Скидання пароля',
                description: 'Введіть дані, щоб встановити новий пароль до акаунта.',
                submit: 'Змінити пароль',
                submitting: 'Зберігаємо…',
                successFallback: 'Пароль успішно змінено. Тепер можна увійти.',
                errorFallback: 'Не вдалося змінити пароль. Перевірте дані та спробуйте ще раз.',
                backToLoginPrefix: 'Повернутися до',
                backToLoginLink: 'сторінки входу',
                backToLoginSuffix: '.',
            },
        },
    },
    wishlist: {
        badge: 'Обране',
        title: 'Обране',
        clear: 'Очистити',
        loading: 'Оновлюємо список бажаного...',
        errorTitle: 'Не вдалося оновити список',
        empty: 'Поки що порожньо.',
        removeAria: ({ name }: { name: string }) => `Прибрати «${name}» зі списку бажаного`,
        noImage: 'без фото',
    },
    product: {
        seo: {
            pageTitle: ({ name, price, brand }: { name: string; price: string; brand: string }) => `${name} — ${price} — ${brand}`,
            fallbackTitle: ({ brand }: { brand: string }) => `Товар — ${brand}`,
            description: ({ name, price, inStock }: { name: string; price: string; inStock: boolean }) =>
                `Купити ${name} за ${price}. ${inStock ? 'В наявності.' : 'Немає в наявності.'} Замовляйте онлайн.`,
            fallbackDescription: 'Картка товару в магазині.',
            breadcrumbHome: 'Головна',
            breadcrumbCatalog: 'Каталог',
        },
        gallery: {
            noImage: 'без фото',
            openImage: ({ index }: { index: number }) => `Відкрити зображення ${index}`,
        },
        reviews: {
            ariaLabel: 'Відгуки',
            title: 'Відгуки',
            summary: {
                loading: 'Завантаження рейтингу…',
                label: 'Середній рейтинг:',
                of: ({ max }: { max: number }) => `із ${max}`,
                empty: 'Ще немає відгуків',
            },
            loading: 'Завантаження відгуків…',
            empty: 'Відгуків поки немає. Станьте першим!',
            anonymous: 'Користувач',
            ratingLabel: ({ value, max }: { value: number; max: number }) => `${value} із ${max}`,
            pending: 'Відгук очікує модерації. Ми сповістимо після публікації.',
        },
        stock: {
            available: ({ count }: { count: number }) => `В наявності: ${count} шт.`,
            unavailable: 'Немає в наявності',
        },
        actions: {
            addToCart: 'Додати в кошик',
            backToCatalog: '← До каталогу',
        },
        toasts: {
            added: {
                title: 'Додано до кошика',
                action: 'Відкрити кошик',
                error: 'Не вдалося додати до кошика',
            },
        },
        tabs: {
            description: 'Опис',
            specs: 'Характеристики',
            delivery: 'Доставка',
        },
        description: {
            empty: 'Опис поки відсутній.',
        },
        specs: {
            empty: 'Характеристики ще не додані.',
        },
        delivery: {
            items: {
                novaPoshta: 'Нова Пошта по Україні — 1–3 дні.',
                courier: 'Курʼєр у великих містах — 1–2 дні.',
                payment: 'Оплата: карткою онлайн або накладений платіж.',
                returns: 'Повернення/обмін — 14 днів (згідно ЗУ «Про захист прав споживачів»).',
            },
        },
        ratingStars: {
            option: ({ value, max }: { value: number; max: number }) => `Оцінка ${value} з ${max}`,
            hint: ({ value, max }: { value: number; max: number }) => `${value} з ${max}`,
        },
        similar: {
            count: ({ count }: { count: number }) => `Знайдено схожих: ${count}`,
        },
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
