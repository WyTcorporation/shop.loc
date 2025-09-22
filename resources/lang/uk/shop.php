<?php

return [
    'admin' => [
        'brand' => 'Панель магазину',
        'navigation' => [
            'catalog' => 'Каталог',
            'sales' => 'Продажі',
            'accounting' => 'Бухгалтерія',
            'inventory' => 'Запаси',
            'marketing' => 'Маркетинг',
            'content' => 'Контент',
            'customers' => 'Клієнти',
            'settings' => 'Налаштування',
        ],
        'language_switcher' => [
            'label' => 'Мова інтерфейсу',
            'help' => 'Змінює мову панелі після оновлення сторінки.',
        ],
        'dashboard' => [
            'filters' => [
                'period' => 'Період',
                'today' => 'Сьогодні',
                'seven_days' => 'Останні 7 днів',
                'thirty_days' => 'Останні 30 днів',
                'ninety_days' => 'Останні 90 днів',
            ],
            'sales' => [
                'title' => 'Показники продажів',
                'revenue' => 'Дохід',
                'orders' => 'Замовлення',
                'average_order_value' => 'Середній чек',
            ],
            'conversion' => [
                'title' => 'Конверсія оформлення',
                'rate' => 'Коефіцієнт конверсії',
                'rate_help' => 'Співвідношення замовлень до кошиків за вибраний період.',
                'orders' => 'Замовлення',
                'carts' => 'Кошики',
            ],
            'traffic' => [
                'title' => 'Джерела трафіку',
                'revenue' => 'Частка доходу',
            ],
            'top_products' => [
                'title' => 'Топ-товари',
                'columns' => [
                    'product' => 'Товар',
                    'sku' => 'SKU',
                    'quantity' => 'Продано одиниць',
                    'revenue' => 'Дохід',
                ],
            ],
            'inventory' => [
                'title' => 'Стан складу',
                'skus' => 'Кількість SKU',
                'available_units' => 'Доступні одиниці',
                'low_stock' => 'Низький залишок (≤ :threshold)',
            ],
        ],
        'resources' => [
            'products' => [
                'label' => 'Товар',
                'plural_label' => 'Товари',
                'imports' => [
                    'tabs' => [
                        'form' => 'Імпорт товарів',
                        'history' => 'Історія',
                    ],
                    'form' => [
                        'heading' => 'Завантажити таблицю',
                        'actions' => [
                            'queue' => 'Додати імпорт у чергу',
                        ],
                    ],
                    'fields' => [
                        'file' => 'Файл імпорту',
                    ],
                    'messages' => [
                        'missing_file' => 'Оберіть файл для імпорту.',
                        'queued_title' => 'Імпорт товарів розпочато',
                        'queued_body' => 'Імпорт виконуватиметься у фоні. Ви отримаєте сповіщення після завершення.',
                        'completed_title' => 'Імпорт товарів завершено',
                        'completed_body' => 'Опрацьовано :processed із :total рядків.',
                        'failed_title' => 'Не вдалося виконати імпорт товарів',
                        'no_rows' => 'У завантаженому файлі не знайдено рядків із даними.',
                        'row_created' => 'Створено товар (:sku)',
                        'row_updated' => 'Оновлено товар (:sku)',
                    ],
                    'table' => [
                        'recent_imports' => 'Останні імпорти',
                        'recent_exports' => 'Останні експорти',
                        'columns' => [
                            'file' => 'Файл',
                            'status' => 'Статус',
                            'progress' => 'Прогрес',
                            'results' => 'Результати',
                            'completed_at' => 'Завершено',
                            'format' => 'Формат',
                            'rows' => 'Рядки',
                        ],
                        'results_created' => 'Створено',
                        'results_updated' => 'Оновлено',
                        'results_failed' => 'Помилки',
                        'empty_imports' => 'Імпортів поки немає.',
                        'empty_exports' => 'Експортів поки немає.',
                    ],
                ],
                'exports' => [
                    'tabs' => [
                        'form' => 'Експорт товарів',
                        'history' => 'Історія',
                    ],
                    'form' => [
                        'heading' => 'Налаштувати експорт',
                        'actions' => [
                            'queue' => 'Додати експорт у чергу',
                        ],
                    ],
                    'fields' => [
                        'file_name' => 'Назва файлу',
                        'format' => 'Формат',
                        'only_active' => 'Лише активні товари',
                    ],
                    'messages' => [
                        'queued_title' => 'Експорт товарів розпочато',
                        'queued_body' => 'Експорт виконуватиметься у фоні. Ви отримаєте сповіщення після завершення.',
                        'completed_title' => 'Експорт товарів завершено',
                        'completed_empty' => 'Не знайдено товарів, що відповідають вибраним фільтрам.',
                        'completed_ready' => 'Експорт товарів готовий до завантаження.',
                        'failed_title' => 'Не вдалося виконати експорт товарів',
                        'download' => 'Завантажити',
                        'pending' => 'Очікує',
                    ],
                    'table' => [
                        'recent_exports' => 'Останні експорти',
                        'recent_imports' => 'Останні імпорти',
                        'columns' => [
                            'format' => 'Формат',
                            'status' => 'Статус',
                            'rows' => 'Рядки',
                            'completed_at' => 'Завершено',
                            'file' => 'Файл',
                        ],
                        'empty_exports' => 'Експортів поки немає.',
                        'empty_imports' => 'Імпортів поки немає.',
                    ],
                ],
            ],
            'categories' => [
                'label' => 'Категорія',
                'plural_label' => 'Категорії',
            ],
            'orders' => [
                'label' => 'Замовлення',
                'plural_label' => 'Замовлення',
            ],
            'vendors' => [
                'label' => 'Продавець',
                'plural_label' => 'Продавці',
            ],
            'inventory' => [
                'label' => 'Позиція запасів',
                'plural_label' => 'Запаси',
            ],
            'coupons' => [
                'label' => 'Купон',
                'plural_label' => 'Купони',
            ],
            'reviews' => [
                'label' => 'Відгук',
                'plural_label' => 'Відгуки',
            ],
            'email_campaigns' => [
                'label' => 'Email-кампанія',
                'plural_label' => 'Email-кампанії',
            ],
            'push_campaigns' => [
                'label' => 'Push-кампанія',
                'plural_label' => 'Push-кампанії',
            ],
            'segments' => [
                'label' => 'Сегмент',
                'plural_label' => 'Сегменти',
            ],
            'tests' => [
                'label' => 'A/B-тест',
                'plural_label' => 'A/B-тести',
            ],
            'users' => [
                'label' => 'Клієнт',
                'plural_label' => 'Клієнти',
            ],
            'roles' => [
                'label' => 'Роль',
                'plural_label' => 'Ролі',
                'form' => [
                    'assign_users_help' => 'Призначте цю роль одному чи кільком користувачам.',
                ],
                'bulk_actions' => [
                    'sync_users' => [
                        'label' => 'Синхронізувати з користувачами',
                        'users_field' => 'Користувачі',
                        'replace_toggle' => 'Замінити наявні ролі для вибраних користувачів',
                    ],
                ],
            ],
            'permissions' => [
                'label' => 'Дозвіл',
                'plural_label' => 'Дозволи',
                'form' => [
                    'assign_users_help' => 'Надайте цей дозвіл безпосередньо конкретним користувачам.',
                ],
                'bulk_actions' => [
                    'sync_users' => [
                        'label' => 'Синхронізувати з користувачами',
                        'users_field' => 'Користувачі',
                        'replace_toggle' => 'Замінити наявні дозволи для вибраних користувачів',
                    ],
                ],
            ],
            'warehouses' => [
                'label' => 'Склад',
                'plural_label' => 'Склади',
            ],
            'currencies' => [
                'label' => 'Валюта',
                'plural_label' => 'Валюти',
            ],
            'invoices' => [
                'label' => 'Рахунок',
                'plural_label' => 'Рахунки',
                'fields' => [
                    'number' => 'Номер',
                    'issued_at' => 'Дата виписки',
                    'due_at' => 'Строк оплати',
                    'subtotal' => 'Проміжна сума',
                    'tax_total' => 'Податок',
                    'metadata' => 'Метадані',
                ],
            ],
            'delivery_notes' => [
                'label' => 'Накладна',
                'plural_label' => 'Накладні',
                'fields' => [
                    'number' => 'Номер',
                    'issued_at' => 'Дата створення',
                    'dispatched_at' => 'Дата відправлення',
                    'items' => 'Позиції',
                    'remarks' => 'Примітки',
                ],
            ],
            'acts' => [
                'label' => 'Акт',
                'plural_label' => 'Акти',
                'fields' => [
                    'number' => 'Номер',
                    'issued_at' => 'Дата підписання',
                    'total' => 'Сума',
                    'description' => 'Опис',
                ],
            ],
            'saft_exports' => [
                'label' => 'SAF-T експорт',
                'plural_label' => 'SAF-T експорти',
                'fields' => [
                    'format' => 'Формат',
                    'exported_at' => 'Дата експорту',
                    'created_at' => 'Створено',
                    'message' => 'Повідомлення',
                    'from_date' => 'Початкова дата',
                    'to_date' => 'Кінцева дата',
                ],
                'status' => [
                    'completed' => 'Завершено',
                    'processing' => 'Виконується',
                    'failed' => 'Помилка',
                ],
                'actions' => [
                    'export' => 'SAF-T експорт',
                    'run' => 'Запустити експорт',
                    'view_logs' => 'Переглянути журнали',
                ],
                'messages' => [
                    'completed' => '{0} SAF-T експорт згенеровано без замовлень|{1} SAF-T експорт згенеровано для :count замовлення|[2,4] SAF-T експорт згенеровано для :count замовлень|[5,*] SAF-T експорт згенеровано для :count замовлень',
                    'success' => 'SAF-T експорт успішно запущено.',
                    'completed_info' => 'Після завершення ви зможете завантажити файл у журналі експортів.',
                    'latest_title' => 'Останній експорт',
                ],
            ],
        ],
    ],

    'navigation' => [
        'catalog' => 'Каталог',
        'cart' => 'Кошик',
        'order' => 'Замовлення',
    ],

    'meta' => [
        'brand' => 'Shop',
    ],

    'languages' => [
        'uk' => 'Українська',
        'en' => 'Англійська',
        'ru' => 'Російська',
        'pt' => 'Португальська',
    ],

    'conversation' => [
        'heading' => 'Розмова',
        'system' => 'Система',
        'empty' => 'Ще немає повідомлень.',
        'new' => 'Нове повідомлення',
        'send' => 'Надіслати',
        'sent' => 'Повідомлення надіслано',
        'message' => 'Повідомлення',
    ],

    'common' => [
        'owner' => 'Власник',
        'email' => 'Електронна адреса',
        'phone' => 'Телефон',
        'footer_note' => 'Якщо виникли питання — просто відповідайте на цей лист.',
        'order_title' => 'Замовлення №:number',
        'updates_email' => 'Ми надішлемо оновлення на :email.',
        'order_number' => 'Номер замовлення',
        'items_total' => 'Сума товарів',
        'coupon' => 'Купон',
        'discount' => 'Знижка',
        'used_points' => 'Використані бали',
        'order_total' => 'Сума замовлення',
        'total_due' => 'До сплати',
        'amount_due' => 'Сума до сплати',
        'status' => 'Статус',
        'shipped' => 'Відправлено',
        'delivered' => 'Доставлено',
        'paid' => 'Оплачено',
        'shipped_at' => 'Дата відправлення',
        'delivered_at' => 'Дата доставки',
        'paid_at' => 'Дата оплати',
        'product' => 'Товар',
        'quantity' => 'К-сть',
        'price' => 'Ціна',
        'sum' => 'Сума',
        'items_subtotal' => 'Разом за товари',
        'name' => 'Імʼя',
        'city' => 'Місто',
        'address' => 'Адреса',
        'postal_code' => 'Поштовий індекс',
        'note' => 'Примітка',
        'total' => 'Разом',
        'tracking_number' => 'Номер відстеження',
        'created' => 'Створено',
        'updated' => 'Оновлено',
        'add' => 'Додати',
        'download' => 'Завантажити',
        'export' => 'Експорт',
    ],

    'auth' => [
        'greeting' => 'Привіт, :name!',
        'reset_link_hint' => 'Посилання для скидання буде надіслано, якщо обліковий запис існує.',
        'reset' => [
            'subject' => 'Скидання пароля для :app',
            'heading' => 'Відновлення доступу до :app',
            'intro' => 'Ви отримали цей лист, бо ми отримали запит на скидання пароля для вашого облікового запису в :app.',
            'title' => 'Скидання пароля',
            'button' => 'Скинути пароль',
            'link_help' => 'Кнопка не працює? Скопіюйте та вставте це посилання у свій браузер:',
            'ignore' => 'Якщо ви не запитували скидання пароля, просто проігноруйте цей лист.',
            'changed_subject' => 'Пароль до :app змінено',
            'changed_title' => 'Пароль змінено',
            'changed_intro' => 'Ми щойно оновили пароль до вашого облікового запису в :app.',
            'changed_warning' => 'Якщо ви не змінювали пароль, негайно звʼяжіться з нашою службою підтримки або скиньте пароль повторно, щоб захистити обліковий запис.',
            'signature' => 'З повагою, команда :app.',
        ],
        'welcome' => [
            'subject' => 'Ласкаво просимо до :app!',
            'title' => 'Ласкаво просимо до :app',
            'intro' => 'Дякуємо за реєстрацію у :app. Щоб завершити створення облікового запису, підтвердіть свою електронну адресу.',
            'button' => 'Підтвердити електронну адресу',
            'ignore' => 'Якщо ви не створювали обліковий запис, просто проігноруйте цей лист.',
        ],
        'verify' => [
            'subject' => 'Підтвердіть електронну адресу для :app',
            'title' => 'Підтвердіть електронну адресу',
            'intro' => 'Щоб активувати свій обліковий запис у :app, підтвердіть електронну адресу протягом наступної години.',
            'button' => 'Підтвердити електронну адресу',
            'ignore' => 'Якщо ви не створювали обліковий запис, просто проігноруйте цей лист.',
        ],
    ],

    'orders' => [
        'placed' => [
            'subject' => 'Дякуємо за замовлення!',
            'subject_line' => 'Ваше замовлення №:number прийнято',
            'intro' => 'Замовлення №:number оформлено.',
        ],
        'paid' => [
            'subject' => 'Оплату отримано',
            'subject_line' => 'Замовлення №:number оплачене',
            'intro' => 'Замовлення №:number успішно оплачене.',
            'next' => 'Ми готуємо його до відправлення та повідомимо про наступні кроки.',
            'button' => 'До магазину',
        ],
        'shipped' => [
            'subject' => 'Замовлення в дорозі',
            'subject_line' => 'Замовлення №:number відправлено',
            'intro' => 'Ми передали замовлення №:number до служби доставки.',
            'next' => 'Надішлемо сповіщення, щойно воно прибуде.',
            'button' => 'Відстежити замовлення',
        ],
        'delivered' => [
            'subject' => 'Замовлення доставлено',
            'subject_line' => 'Замовлення №:number доставлено',
            'intro' => 'Замовлення №:number успішно доставлене.',
            'thanks' => 'Сподіваємося, що вам сподобалися покупки. Дякуємо, що обрали :app!',
            'button' => 'Переглянути замовлення',
        ],
        'status_updated' => [
            'subject_line' => 'Ваше замовлення №:number: статус оновлено',
        ],
        'sections' => [
            'general' => 'Загальне',
            'shipping' => 'Доставка',
            'shipment' => 'Відправлення',
            'summary' => 'Підсумок',
        ],
        'fieldsets' => [
            'shipping_address' => 'Адреса доставки',
            'billing_address' => 'Платіжна адреса',
        ],
        'fields' => [
            'user' => 'Користувач',
            'number' => 'Номер',
            'total' => 'Всього',
            'shipment_status' => 'Статус відправлення',
            'currency' => 'Валюта',
        ],
        'helpers' => [
            'email_auto' => 'Якщо вибрано користувача, електронна адреса заповниться автоматично.',
        ],
        'placeholders' => [
            'any_order' => 'Будь-яке замовлення',
        ],
        'hints' => [
            'number_generated' => 'Згенерується автоматично',
        ],
        'actions' => [
            'messages' => 'Повідомлення',
            'mark_paid' => 'Позначити оплаченим',
            'mark_shipped' => 'Позначити відправленим',
            'cancel' => 'Скасувати',
            'resend_confirmation' => 'Надіслати підтвердження',
        ],
        'notifications' => [
            'marked_paid' => 'Замовлення позначене як оплачене',
            'marked_shipped' => 'Замовлення позначене як відправлене',
            'cancelled' => 'Замовлення скасовано',
            'confirmation_resent' => 'Лист-підтвердження повторно надіслано',
        ],
        'summary' => [
            'positions' => 'Позиції',
            'subtotal' => 'Проміжна сума',
            'total_order' => 'Разом (замовлення)',
        ],
        'shipment_status' => [
            'pending' => 'Очікує',
            'processing' => 'Опрацьовується',
            'shipped' => 'Відправлено',
            'delivered' => 'Доставлено',
            'cancelled' => 'Скасовано',
        ],
        'statuses' => [
            'new' => 'нове',
            'paid' => 'оплачене',
            'shipped' => 'відправлене',
            'cancelled' => 'скасоване',
        ],
        'items' => [
            'title' => 'Товари у замовленні',
            'fields' => [
                'product' => 'Товар',
                'qty' => 'Кількість',
                'price' => 'Ціна',
                'subtotal' => 'Проміжна сума',
            ],
            'empty_state' => [
                'heading' => 'Ще немає товарів',
            ],
        ],
        'logs' => [
            'title' => 'Історія статусів',
            'fields' => [
                'from' => 'Було',
                'to' => 'Стало',
                'by' => 'Ким змінено',
                'note' => 'Примітка',
                'deleted_at' => 'Видалено',
                'created_at' => 'Створено',
                'updated_at' => 'Оновлено',
            ],
            'empty_state' => [
                'heading' => 'Статус ще не змінювався',
            ],
        ],
        'errors' => [
            'only_new_can_be_marked_paid' => 'Позначати оплаченим можна лише замовлення зі статусом ":required". Замовлення №:number зараз має статус ":status".',
            'only_paid_can_be_marked_shipped' => 'Позначати відправленим можна лише замовлення зі статусом ":required". Замовлення №:number зараз має статус ":status".',
            'only_new_or_paid_can_be_cancelled' => 'Скасувати можна лише замовлення зі статусами: :allowed. Замовлення №:number зараз має статус ":status".',
        ],
    ],

    'inventory' => [
        'not_enough_stock' => 'Недостатньо товару для продукту №:product_id на складі №:warehouse_id.',
        'not_enough_reserved_stock' => 'Недостатньо зарезервованого товару для продукту №:product_id на складі №:warehouse_id.',
        'fields' => [
            'product' => 'Продукт',
            'warehouse' => 'Склад',
            'quantity' => 'Кількість',
            'reserved' => 'Зарезервовано',
            'available' => 'Доступно',
        ],
        'filters' => [
            'warehouse' => 'Склад',
        ],
    ],

    'warehouses' => [
        'fields' => [
            'code' => 'Код',
            'name' => 'Назва',
            'description' => 'Опис',
        ],
        'columns' => [
            'created' => 'Створено',
            'updated' => 'Оновлено',
        ],
    ],

    'coupons' => [
        'fields' => [
            'code' => 'Код',
            'type' => 'Тип',
            'value' => 'Значення',
            'min_cart' => 'Мін. сума кошика',
            'max_discount' => 'Макс. знижка',
            'usage' => 'Використання',
            'usage_limit' => 'Загальне обмеження',
            'per_user_limit' => 'Обмеження на користувача',
            'starts_at' => 'Початок дії',
            'expires_at' => 'Завершення дії',
            'is_active' => 'Активний',
        ],
        'filters' => [
            'is_active' => 'Активний',
        ],
        'helpers' => [
            'code_unique' => 'Унікальний код купона, який вводитимуть клієнти.',
        ],
        'types' => [
            'fixed' => 'Фіксована сума',
            'percent' => 'Відсоток',
        ],
    ],

    'reviews' => [
        'fields' => [
            'product' => 'Товар',
            'user' => 'Користувач',
            'rating' => 'Оцінка',
            'status' => 'Статус',
            'text' => 'Текст відгуку',
            'created_at' => 'Створено',
        ],
        'filters' => [
            'status' => 'Статус',
        ],
        'statuses' => [
            'pending' => 'Очікує',
            'approved' => 'Схвалено',
            'rejected' => 'Відхилено',
        ],
    ],

    'users' => [
        'fields' => [
            'points_balance' => 'Баланс балів',
            'password' => 'Пароль',
            'roles' => 'Ролі',
            'permissions' => 'Прямі дозволи',
            'categories' => 'Дозволені категорії',
        ],
    ],

    'api' => [
        'common' => [
            'not_found' => 'Ресурс не знайдено.',
        ],
        'auth' => [
            'unauthenticated' => 'Користувач не авторизований.',
            'verification_link_sent' => 'Посилання для підтвердження надіслано.',
            'two_factor_required' => 'Потрібен код двофакторної автентифікації.',
            'invalid_two_factor_code' => 'Невірний код двофакторної автентифікації.',
        ],
        'verify_email' => [
            'invalid_signature' => 'Недійсний підпис для підтвердження електронної адреси.',
            'already_verified' => 'Електронна адреса вже підтверджена.',
            'verified' => 'Електронну адресу підтверджено.',
        ],
        'cart' => [
            'not_enough_stock' => 'Недостатньо товару на складі',
            'coupon_not_found' => 'Купон не знайдено.',
            'coupon_not_applicable' => 'Купон не можна застосувати до цього кошика.',
            'points_auth_required' => 'Лише авторизовані користувачі можуть використати бонусні бали.',
        ],
        'orders' => [
            'cart_empty' => 'Кошик порожній',
            'insufficient_stock' => 'Недостатньо товару для продукту №:product',
            'coupon_unavailable' => 'Купон більше недоступний.',
            'coupon_usage_limit_reached' => 'Перевищено ліміт використання купона.',
            'not_enough_points' => 'Недостатньо бонусних балів для списання запитаної кількості.',
            'points_redeemed_description' => 'Бали списано за замовлення :number',
            'points_earned_description' => 'Бали нараховано за замовлення :number',
        ],
        'reviews' => [
            'submitted' => 'Відгук надіслано на модерацію.',
        ],
        'payments' => [
            'missing_intent' => 'Відсутній payment_intent.',
            'invalid_signature' => 'Недійсний підпис Stripe.',
        ],
    ],

    'loyalty' => [
        'transaction' => [
            'earn' => 'Нараховано :points балів лояльності.',
            'redeem' => 'Списано :points балів лояльності.',
            'adjustment' => 'Баланс змінено на :points.',
        ],
        'demo' => [
            'checkout_redeem' => 'Бали списано під час оформлення замовлення',
            'shipped_bonus' => 'Бонус за відправлене замовлення :number',
            'cancellation_return' => 'Бали повернено після скасування',
        ],
        'transactions' => [
            'fields' => [
                'type' => 'Тип',
                'points' => 'Бали',
                'amount' => 'Сума',
                'description' => 'Опис',
            ],
            'types' => [
                'earn' => 'Нарахування',
                'redeem' => 'Списання',
                'adjustment' => 'Коригування',
            ],
        ],
    ],

    'widgets' => [
        'marketing_performance' => [
            'title' => 'Ефективність маркетингу',
            'stats' => [
                'email_opens' => 'Відкриття email',
                'push_clicks' => 'Кліки push',
                'total_conversions' => 'Усього конверсій',
            ],
            'descriptions' => [
                'avg_conversion' => 'Середня конверсія: :rate%',
            ],
        ],
        'orders_stats' => [
            'labels' => [
                'new' => 'Нові',
                'paid' => 'Оплачені',
                'shipped' => 'Відправлені',
                'cancelled' => 'Скасовані',
            ],
            'descriptions' => [
                'new' => 'Очікує',
            ],
        ],
    ],

    'products' => [
        'fields' => [
            'name' => 'Назва',
            'slug' => 'Слаг',
            'description' => 'Опис',
            'sku' => 'Артикул',
            'category' => 'Категорія',
            'vendor' => 'Постачальник',
            'preview' => 'Зображення',
            'preview_url_debug' => 'URL?',
            'stock' => 'Залишок',
            'price' => 'Ціна',
            'price_old' => 'Стара ціна',
            'is_active' => 'Активний',
        ],
        'attributes' => [
            'label' => 'Атрибути',
            'name' => 'Назва',
            'value' => 'Значення',
            'add' => 'Додати атрибут',
            'translations' => 'Переклади',
        ],
        'placeholders' => [
            'available_stock' => 'Доступний залишок',
        ],
        'filters' => [
            'category' => 'Категорія',
            'is_active' => [
                'label' => 'Активність',
                'true' => 'Активні',
                'false' => 'Неактивні',
            ],
        ],
        'images' => [
            'title'=>'Зображення',
            'fields' => [
                'image' => 'Зображення',
                'alt_text' => 'Альтернативний текст',
                'is_primary' => 'Головне зображення',
                'preview' => 'Превʼю',
                'disk' => 'Диск',
                'sort' => 'Порядок',
                'created_at' => 'Створено',
            ],
            'helper_texts' => [
                'is_primary' => 'Використовується як превʼю товару.',
            ],
            'actions' => [
                'create' => 'Додати',
                'edit' => 'Редагувати',
                'delete' => 'Видалити',
            ],
            'empty' => [
                'heading' => 'Ще немає зображень',
                'description' => 'Додайте зображення товару, щоб побачити їх тут.',
            ],
        ],
    ],

    'categories' => [
        'fields' => [
            'name' => 'Назва',
            'slug' => 'Слаг',
            'parent' => 'Батьківська категорія',
            'deleted_at' => 'Видалено',
            'created_at' => 'Створено',
            'updated_at' => 'Оновлено',
        ],
    ],

    'vendor' => [
        'fields' => [
            'name' => 'Назва',
            'slug' => 'Слаг',
            'description' => 'Опис',

            'deleted_at' => 'Видалено',
            'created_at' => 'Створено',
            'updated_at' => 'Оновлено',
        ],
    ],

    'currencies' => [
        'navigation_group' => 'Налаштування',
        'code' => 'Код',
        'rate' => 'Курс',
        'rate_vs_base' => 'Курс (до базової)',
        'updated' => 'Оновлено',
    ],

    'security' => [
        'two_factor' => [
            'not_initialized' => 'Двофакторну автентифікацію не налаштовано.',
            'invalid_code' => 'Недійсний код двофакторної автентифікації.',
            'enabled' => 'Двофакторну автентифікацію увімкнено.',
        ],
    ],
];
