<?php

return [
    'languages' => [
        'uk' => 'Украинский',
        'en' => 'Английский',
        'ru' => 'Русский',
        'pt' => 'Португальский',
    ],
    'admin' => [
        'brand' => 'Панель магазина',
        'navigation' => [
            'catalog' => 'Каталог',
            'sales' => 'Продажи',
            'accounting' => 'Бухгалтерия',
            'inventory' => 'Запасы',
            'marketing' => 'Маркетинг',
            'content' => 'Контент',
            'customers' => 'Клиенты',
            'settings' => 'Настройки',
        ],
        'language_switcher' => [
            'label' => 'Язык интерфейса',
            'help' => 'Изменяет язык панели после обновления страницы.',
        ],
        'dashboard' => [
            'filters' => [
                'period' => 'Период',
                'today' => 'Сегодня',
                'seven_days' => 'Последние 7 дней',
                'thirty_days' => 'Последние 30 дней',
                'ninety_days' => 'Последние 90 дней',
            ],
            'sales' => [
                'title' => 'Показатели продаж',
                'revenue' => 'Выручка',
                'orders' => 'Заказы',
                'average_order_value' => 'Средний чек',
            ],
            'conversion' => [
                'title' => 'Конверсия оформления',
                'rate' => 'Коэффициент конверсии',
                'rate_help' => 'Соотношение заказов и корзин за выбранный период.',
                'orders' => 'Заказы',
                'carts' => 'Корзины',
            ],
            'traffic' => [
                'title' => 'Источники трафика',
                'revenue' => 'Доля выручки',
            ],
            'top_products' => [
                'title' => 'Топ-товары',
                'columns' => [
                    'product' => 'Товар',
                    'sku' => 'SKU',
                    'quantity' => 'Продано единиц',
                    'revenue' => 'Выручка',
                ],
            ],
            'inventory' => [
                'title' => 'Состояние склада',
                'skus' => 'Количество SKU',
                'available_units' => 'Доступные единицы',
                'low_stock' => 'Низкий остаток (≤ :threshold)',
            ],
        ],
        'resources' => [
            'products' => [
                'label' => 'Товар',
                'plural_label' => 'Товары',
                'imports' => [
                    'tabs' => [
                        'form' => 'Импорт товаров',
                        'history' => 'История',
                    ],
                    'form' => [
                        'heading' => 'Загрузить таблицу',
                        'actions' => [
                            'queue' => 'Поставить импорт в очередь',
                        ],
                    ],
                    'fields' => [
                        'file' => 'Файл импорта',
                    ],
                    'messages' => [
                        'missing_file' => 'Выберите файл для импорта.',
                        'queued_title' => 'Импорт товаров запущен',
                        'queued_body' => 'Импорт будет выполнен в фоне. Вы получите уведомление после завершения.',
                        'completed_title' => 'Импорт товаров завершён',
                        'completed_body' => 'Обработано :processed из :total строк.',
                        'failed_title' => 'Не удалось выполнить импорт товаров',
                        'no_rows' => 'В загруженном файле не обнаружено строк с данными.',
                        'row_created' => 'Создан товар (:sku)',
                        'row_updated' => 'Обновлён товар (:sku)',
                        'category_forbidden' => 'Категория не разрешена для текущей роли.',
                    ],
                    'table' => [
                        'recent_imports' => 'Последние импорты',
                        'recent_exports' => 'Последние экспорты',
                        'columns' => [
                            'file' => 'Файл',
                            'status' => 'Статус',
                            'progress' => 'Прогресс',
                            'results' => 'Результаты',
                            'completed_at' => 'Завершено',
                            'format' => 'Формат',
                            'rows' => 'Строки',
                        ],
                        'results_created' => 'Создано',
                        'results_updated' => 'Обновлено',
                        'results_failed' => 'Ошибки',
                        'empty_imports' => 'Импортов пока нет.',
                        'empty_exports' => 'Экспортов пока нет.',
                    ],
                ],
                'exports' => [
                    'tabs' => [
                        'form' => 'Экспорт товаров',
                        'history' => 'История',
                    ],
                    'form' => [
                        'heading' => 'Настроить экспорт',
                        'actions' => [
                            'queue' => 'Поставить экспорт в очередь',
                        ],
                    ],
                    'fields' => [
                        'file_name' => 'Имя файла',
                        'format' => 'Формат',
                        'only_active' => 'Только активные товары',
                    ],
                    'messages' => [
                        'queued_title' => 'Экспорт товаров запущен',
                        'queued_body' => 'Экспорт будет выполнен в фоне. Вы получите уведомление после завершения.',
                        'completed_title' => 'Экспорт товаров завершён',
                        'completed_empty' => 'Не найдено товаров, соответствующих выбранным фильтрам.',
                        'completed_ready' => 'Экспорт товаров готов к загрузке.',
                        'failed_title' => 'Не удалось выполнить экспорт товаров',
                        'download' => 'Скачать',
                        'pending' => 'Ожидание',
                    ],
                    'table' => [
                        'recent_exports' => 'Последние экспорты',
                        'recent_imports' => 'Последние импорты',
                        'columns' => [
                            'format' => 'Формат',
                            'status' => 'Статус',
                            'rows' => 'Строки',
                            'completed_at' => 'Завершено',
                            'file' => 'Файл',
                        ],
                        'empty_exports' => 'Экспортов пока нет.',
                        'empty_imports' => 'Импортов пока нет.',
                    ],
                ],
            ],
            'categories' => [
                'label' => 'Категория',
                'plural_label' => 'Категории',
            ],
            'orders' => [
                'label' => 'Заказ',
                'plural_label' => 'Заказы',
            ],
            'vendors' => [
                'label' => 'Поставщик',
                'plural_label' => 'Поставщики',
            ],
            'inventory' => [
                'label' => 'Запас',
                'plural_label' => 'Запасы',
            ],
            'coupons' => [
                'label' => 'Купон',
                'plural_label' => 'Купоны',
            ],
            'reviews' => [
                'label' => 'Отзыв',
                'plural_label' => 'Отзывы',
            ],
            'email_campaigns' => [
                'label' => 'Email-кампания',
                'plural_label' => 'Email-кампании',
            ],
            'push_campaigns' => [
                'label' => 'Push-кампания',
                'plural_label' => 'Push-кампании',
            ],
            'segments' => [
                'label' => 'Сегмент',
                'plural_label' => 'Сегменты',
            ],
            'tests' => [
                'label' => 'A/B-тест',
                'plural_label' => 'A/B-тесты',
            ],
            'users' => [
                'label' => 'Покупатель',
                'plural_label' => 'Покупатели',
            ],
            'roles' => [
                'label' => 'Роль',
                'plural_label' => 'Роли',
                'form' => [
                    'assign_users_help' => 'Назначьте эту роль одному или нескольким пользователям.',
                ],
                'bulk_actions' => [
                    'sync_users' => [
                        'label' => 'Синхронизировать с пользователями',
                        'users_field' => 'Пользователи',
                        'replace_toggle' => 'Заменить существующие роли у выбранных пользователей',
                    ],
                ],
            ],
            'permissions' => [
                'label' => 'Разрешение',
                'plural_label' => 'Разрешения',
                'form' => [
                    'assign_users_help' => 'Назначьте это разрешение конкретным пользователям напрямую.',
                ],
                'bulk_actions' => [
                    'sync_users' => [
                        'label' => 'Синхронизировать с пользователями',
                        'users_field' => 'Пользователи',
                        'replace_toggle' => 'Заменить существующие разрешения у выбранных пользователей',
                    ],
                ],
            ],
            'warehouses' => [
                'label' => 'Склад',
                'plural_label' => 'Склады',
            ],
            'currencies' => [
                'label' => 'Валюта',
                'plural_label' => 'Валюты',
            ],
            'invoices' => [
                'label' => 'Счет',
                'plural_label' => 'Счета',
                'fields' => [
                    'number' => 'Номер',
                    'issued_at' => 'Дата выставления',
                    'due_at' => 'Срок оплаты',
                    'subtotal' => 'Промежуточный итог',
                    'tax_total' => 'Налог',
                    'metadata' => 'Метаданные',
                ],
            ],
            'delivery_notes' => [
                'label' => 'Накладная',
                'plural_label' => 'Накладные',
                'fields' => [
                    'number' => 'Номер',
                    'issued_at' => 'Дата создания',
                    'dispatched_at' => 'Дата отправки',
                    'items' => 'Позиции',
                    'remarks' => 'Примечания',
                ],
            ],
            'acts' => [
                'label' => 'Акт',
                'plural_label' => 'Акты',
                'fields' => [
                    'number' => 'Номер',
                    'issued_at' => 'Дата подписания',
                    'total' => 'Сумма',
                    'description' => 'Описание',
                ],
            ],
            'saft_exports' => [
                'label' => 'SAF-T экспорт',
                'plural_label' => 'SAF-T экспорты',
                'fields' => [
                    'format' => 'Формат',
                    'exported_at' => 'Дата экспорта',
                    'created_at' => 'Создано',
                    'message' => 'Сообщение',
                    'from_date' => 'Дата начала',
                    'to_date' => 'Дата окончания',
                ],
                'status' => [
                    'completed' => 'Завершен',
                    'processing' => 'В обработке',
                    'failed' => 'Ошибка',
                ],
                'actions' => [
                    'export' => 'SAF-T экспорт',
                    'run' => 'Запустить экспорт',
                    'view_logs' => 'Просмотр журналов',
                ],
                'messages' => [
                    'completed' => '{0} SAF-T экспорт создан без заказов|{1} SAF-T экспорт создан для :count заказа|[2,4] SAF-T экспорт создан для :count заказов|[5,*] SAF-T экспорт создан для :count заказов',
                    'success' => 'SAF-T экспорт успешно запущен.',
                    'completed_info' => 'После завершения вы сможете скачать файл в списке журналов.',
                    'latest_title' => 'Последний экспорт',
                ],
            ],
        ],
    ],

    'navigation' => [
        'catalog' => 'Каталог',
        'cart' => 'Корзина',
        'order' => 'Заказ',
    ],

    'meta' => [
        'brand' => 'Shop',
    ],

    'languages' => [
        'uk' => 'Украинский',
        'en' => 'Английский',
        'ru' => 'Русский',
        'pt' => 'Португальский',
    ],

    'conversation' => [
        'heading' => 'Переписка',
        'system' => 'Система',
        'empty' => 'Сообщений пока нет.',
        'new' => 'Новое сообщение',
        'send' => 'Отправить',
        'sent' => 'Сообщение отправлено',
        'message' => 'Сообщение',
    ],

    'common' => [
        'owner' => 'Владелец',
        'email' => 'Электронная почта',
        'phone' => 'Телефон',
        'footer_note' => 'Если возникли вопросы — просто ответьте на это письмо.',
        'order_title' => 'Заказ №:number',
        'updates_email' => 'Мы отправим обновления на :email.',
        'order_number' => 'Номер заказа',
        'items_total' => 'Сумма товаров',
        'coupon' => 'Купон',
        'discount' => 'Скидка',
        'used_points' => 'Использованные баллы',
        'order_total' => 'Сумма заказа',
        'total_due' => 'К оплате',
        'amount_due' => 'Сумма к оплате',
        'status' => 'Статус',
        'shipped' => 'Отправлен',
        'delivered' => 'Доставлен',
        'paid' => 'Оплачен',
        'shipped_at' => 'Дата отправки',
        'delivered_at' => 'Дата доставки',
        'paid_at' => 'Дата оплаты',
        'product' => 'Товар',
        'quantity' => 'Кол-во',
        'price' => 'Цена',
        'sum' => 'Итого',
        'items_subtotal' => 'Итого за товары',
        'name' => 'Имя',
        'city' => 'Город',
        'address' => 'Адрес',
        'postal_code' => 'Почтовый индекс',
        'note' => 'Примечание',
        'total' => 'Итого',
        'tracking_number' => 'Номер отслеживания',
        'delivery_method' => 'Способ доставки',
        'created' => 'Создано',
        'updated' => 'Обновлено',
        'add' => 'Добавить',
        'download' => 'Скачать',
        'export' => 'Экспорт',
    ],

    'auth' => [
        'greeting' => 'Здравствуйте, :name!',
        'reset_link_hint' => 'Ссылка для сброса будет отправлена, если аккаунт существует.',
        'reset' => [
            'subject' => 'Сброс пароля для :app',
            'heading' => 'Восстановление доступа к :app',
            'intro' => 'Вы получили это письмо, потому что запросили сброс пароля для аккаунта в :app.',
            'title' => 'Сброс пароля',
            'button' => 'Сбросить пароль',
            'link_help' => 'Кнопка не работает? Скопируйте и вставьте эту ссылку в браузер:',
            'ignore' => 'Если вы не запрашивали сброс пароля, просто проигнорируйте это письмо.',
            'changed_subject' => 'Пароль для :app изменён',
            'changed_title' => 'Пароль изменён',
            'changed_intro' => 'Мы только что обновили пароль вашего аккаунта в :app.',
            'changed_warning' => 'Если вы не меняли пароль, свяжитесь со службой поддержки или немедленно сбросьте его повторно, чтобы защитить аккаунт.',
            'signature' => 'С уважением, команда :app.',
        ],
        'welcome' => [
            'subject' => 'Добро пожаловать в :app!',
            'title' => 'Добро пожаловать в :app',
            'intro' => 'Спасибо за регистрацию в :app. Чтобы завершить настройку аккаунта, подтвердите свой адрес электронной почты.',
            'button' => 'Подтвердить адрес электронной почты',
            'ignore' => 'Если вы не создавали аккаунт, просто проигнорируйте это письмо.',
        ],
        'verify' => [
            'subject' => 'Подтвердите адрес электронной почты для :app',
            'title' => 'Подтвердите адрес электронной почты',
            'intro' => 'Чтобы активировать аккаунт в :app, подтвердите адрес электронной почты в течение ближайшего часа.',
            'button' => 'Подтвердить адрес электронной почты',
            'ignore' => 'Если вы не создавали аккаунт, просто проигнорируйте это письмо.',
        ],
    ],

    'orders' => [
        'placed' => [
            'subject' => 'Спасибо за заказ!',
            'subject_line' => 'Ваш заказ №:number принят',
            'intro' => 'Заказ №:number оформлен.',
        ],
        'paid' => [
            'subject' => 'Платёж получен',
            'subject_line' => 'Заказ №:number оплачен',
            'intro' => 'Заказ №:number успешно оплачен.',
            'next' => 'Мы готовим его к отправке и сообщим о следующих шагах.',
            'button' => 'В магазин',
        ],
        'shipped' => [
            'subject' => 'Заказ в пути',
            'subject_line' => 'Заказ №:number отправлен',
            'intro' => 'Мы передали заказ №:number службе доставки.',
            'next' => 'Мы уведомим вас, как только он прибудет.',
            'button' => 'Отследить заказ',
        ],
        'delivered' => [
            'subject' => 'Заказ доставлен',
            'subject_line' => 'Заказ №:number доставлен',
            'intro' => 'Заказ №:number успешно доставлен.',
            'thanks' => 'Надеемся, вам понравились покупки. Спасибо, что выбираете :app!',
            'button' => 'Посмотреть заказ',
        ],
        'status_updated' => [
            'subject_line' => 'Статус вашего заказа №:number обновлён',
            'heading' => 'Статус заказа обновлён',
            'order_intro' => 'Заказ №:number',
            'labels' => [
                'from' => 'Было',
                'to' => 'Стало',
                'subtotal' => 'Сумма товаров',
                'coupon' => 'Купон',
                'discount' => 'Скидка',
                'loyalty_points' => 'Использованные баллы',
                'total' => 'К оплате',
                'status' => 'Статус',
                'date' => 'Дата',
            ],
            'thanks' => 'Спасибо за покупку!',
            'team_signature' => 'Команда :app',
        ],
        'sections' => [
            'general' => 'Общее',
            'shipping' => 'Доставка',
            'shipment' => 'Отгрузка',
            'summary' => 'Сводка',
        ],
        'fieldsets' => [
            'shipping_address' => 'Адрес доставки',
            'billing_address' => 'Платежный адрес',
        ],
        'fields' => [
            'user' => 'Пользователь',
            'number' => 'Номер',
            'total' => 'Итого',
            'shipment_status' => 'Статус отгрузки',
            'currency' => 'Валюта',
        ],
        'helpers' => [
            'email_auto' => 'Если выбран пользователь, адрес заполнится автоматически.',
        ],
        'placeholders' => [
            'any_order' => 'Любой заказ',
        ],
        'hints' => [
            'number_generated' => 'Сгенерируется автоматически',
        ],
        'actions' => [
            'messages' => 'Сообщения',
            'mark_paid' => 'Отметить оплаченным',
            'mark_shipped' => 'Отметить отправленным',
            'cancel' => 'Отменить',
            'resend_confirmation' => 'Отправить подтверждение снова',
        ],
        'notifications' => [
            'marked_paid' => 'Заказ отмечен как оплаченный',
            'marked_shipped' => 'Заказ отмечен как отправленный',
            'cancelled' => 'Заказ отменён',
            'confirmation_resent' => 'Письмо-подтверждение отправлено повторно',
        ],
        'summary' => [
            'positions' => 'Позиции',
            'subtotal' => 'Промежуточный итог',
            'total_order' => 'Итого (заказ)',
        ],
        'shipment_status' => [
            'pending' => 'В ожидании',
            'processing' => 'Обрабатывается',
            'shipped' => 'Отправлено',
            'delivered' => 'Доставлено',
            'cancelled' => 'Отменено',
        ],
        'statuses' => [
            'new' => 'новый',
            'paid' => 'оплачен',
            'shipped' => 'отправлен',
            'cancelled' => 'отменён',
        ],
        'items' => [
            'title' => 'Товары в заказе',
            'fields' => [
                'product' => 'Товар',
                'qty' => 'Количество',
                'price' => 'Цена',
                'subtotal' => 'Промежуточный итог',
            ],
            'empty_state' => [
                'heading' => 'Пока нет товаров',
            ],
        ],
        'logs' => [
            'title' => 'История статусов',
            'fields' => [
                'from' => 'Было',
                'to' => 'Стало',
                'by' => 'Кем изменено',
                'note' => 'Примечание',
                'deleted_at' => 'Удалено',
                'created_at' => 'Создано',
                'updated_at' => 'Обновлено',
            ],
            'empty_state' => [
                'heading' => 'Статус ещё не изменялся',
            ],
            'shipment_status_note' => 'Обновлён статус отправления: :status',
        ],
        'errors' => [
            'only_new_can_be_marked_paid' => 'Отметить оплаченным можно только заказ со статусом ":required". Заказ №:number сейчас имеет статус ":status".',
            'only_paid_can_be_marked_shipped' => 'Отметить отправленным можно только заказ со статусом ":required". Заказ №:number сейчас имеет статус ":status".',
            'only_new_or_paid_can_be_cancelled' => 'Отменить можно только заказы со статусами: :allowed. Заказ №:number сейчас имеет статус ":status".',
        ],
    ],

    'inventory' => [
        'not_enough_stock' => 'Недостаточно товара для продукта №:product_id на складе №:warehouse_id.',
        'not_enough_reserved_stock' => 'Недостаточно зарезервированного товара для продукта №:product_id на складе №:warehouse_id.',
        'fields' => [
            'product' => 'Продукт',
            'warehouse' => 'Склад',
            'quantity' => 'Количество',
            'reserved' => 'Зарезервировано',
            'available' => 'Доступно',
        ],
        'filters' => [
            'warehouse' => 'Склад',
        ],
    ],

    'warehouses' => [
        'fields' => [
            'code' => 'Код',
            'name' => 'Название',
            'description' => 'Описание',
        ],
        'columns' => [
            'created' => 'Создано',
            'updated' => 'Обновлено',
        ],
    ],

    'coupons' => [
        'fields' => [
            'code' => 'Код',
            'type' => 'Тип',
            'value' => 'Значение',
            'min_cart' => 'Мин. сумма корзины',
            'max_discount' => 'Макс. скидка',
            'usage' => 'Использование',
            'usage_limit' => 'Общий лимит использования',
            'per_user_limit' => 'Лимит на пользователя',
            'starts_at' => 'Начало действия',
            'expires_at' => 'Окончание действия',
            'is_active' => 'Активен',
        ],
        'filters' => [
            'is_active' => 'Активность',
        ],
        'helpers' => [
            'code_unique' => 'Уникальный код, который будут вводить покупатели.',
        ],
        'types' => [
            'fixed' => 'Фиксированная сумма',
            'percent' => 'Процент',
        ],
    ],

    'reviews' => [
        'fields' => [
            'product' => 'Товар',
            'user' => 'Пользователь',
            'rating' => 'Рейтинг',
            'status' => 'Статус',
            'text' => 'Текст отзыва',
            'created_at' => 'Создано',
        ],
        'filters' => [
            'status' => 'Статус',
        ],
        'statuses' => [
            'pending' => 'На модерации',
            'approved' => 'Одобрен',
            'rejected' => 'Отклонён',
        ],
    ],

    'users' => [
        'fields' => [
            'points_balance' => 'Баланс баллов',
            'password' => 'Пароль',
            'roles' => 'Роли',
            'permissions' => 'Прямые разрешения',
            'categories' => 'Разрешённые категории',
        ],
    ],

    'api' => [
        'common' => [
            'not_found' => 'Ресурс не найден.',
        ],
        'auth' => [
            'unauthenticated' => 'Пользователь не авторизован.',
            'verification_link_sent' => 'Ссылка для подтверждения отправлена.',
            'two_factor_required' => 'Требуется код двухфакторной аутентификации.',
            'invalid_two_factor_code' => 'Неверный код двухфакторной аутентификации.',
        ],
        'verify_email' => [
            'invalid_signature' => 'Недействительная подпись для подтверждения электронной почты.',
            'already_verified' => 'Электронная почта уже подтверждена.',
            'verified' => 'Электронная почта подтверждена.',
        ],
        'cart' => [
            'not_enough_stock' => 'Недостаточно товара на складе',
            'coupon_not_found' => 'Купон не найден.',
            'coupon_not_applicable' => 'Купон нельзя применить к этой корзине.',
            'points_auth_required' => 'Только авторизованные пользователи могут использовать бонусные баллы.',
        ],
        'orders' => [
            'cart_empty' => 'Корзина пуста',
            'insufficient_stock' => 'Недостаточно товара для продукта №:product',
            'sold_out' => 'Товар закончился на всех складах. Приносим извинения за неудобства.',
            'coupon_unavailable' => 'Купон больше недоступен.',
            'coupon_usage_limit_reached' => 'Достигнут лимит использования купона.',
            'not_enough_points' => 'Недостаточно бонусных баллов для списания запрошенной суммы.',
            'points_redeemed_description' => 'Баллы списаны за заказ :number',
            'points_earned_description' => 'Баллы начислены за заказ :number',
        ],
        'reviews' => [
            'submitted' => 'Отзыв отправлен на модерацию.',
        ],
        'payments' => [
            'missing_intent' => 'Отсутствует payment_intent.',
            'invalid_signature' => 'Недействительная подпись Stripe.',
        ],
    ],

    'loyalty' => [
        'transaction' => [
            'earn' => 'Начислено :points баллов лояльности.',
            'redeem' => 'Списано :points баллов лояльности.',
            'adjustment' => 'Баланс изменён на :points.',
        ],
        'demo' => [
            'checkout_redeem' => 'Баллы списаны при оформлении заказа',
            'shipped_bonus' => 'Бонус за отправленный заказ :number',
            'cancellation_return' => 'Баллы возвращены после отмены',
        ],
        'transactions' => [
            'fields' => [
                'type' => 'Тип',
                'points' => 'Баллы',
                'amount' => 'Сумма',
                'description' => 'Описание',
            ],
            'types' => [
                'earn' => 'Начисление',
                'redeem' => 'Списание',
                'adjustment' => 'Корректировка',
            ],
        ],
    ],

    'widgets' => [
        'marketing_performance' => [
            'title' => 'Эффективность маркетинга',
            'stats' => [
                'email_opens' => 'Открытия email',
                'push_clicks' => 'Клики push',
                'total_conversions' => 'Всего конверсий',
            ],
            'descriptions' => [
                'avg_conversion' => 'Средняя конверсия: :rate%',
            ],
        ],
        'orders_stats' => [
            'labels' => [
                'new' => 'Новые',
                'paid' => 'Оплаченные',
                'shipped' => 'Отправленные',
                'cancelled' => 'Отменённые',
            ],
            'descriptions' => [
                'new' => 'В ожидании',
            ],
        ],
    ],

    'products' => [
        'fields' => [
            'name' => 'Название',
            'slug' => 'Слаг',
            'description' => 'Описание',
            'sku' => 'Артикул',
            'category' => 'Категория',
            'vendor' => 'Поставщик',
            'preview' => 'Превью',
            'preview_url_debug' => 'URL?',
            'stock' => 'Остаток',
            'price' => 'Цена',
            'price_old' => 'Старая цена',
            'is_active' => 'Активен',
        ],
        'attributes' => [
            'label' => 'Атрибуты',
            'name' => 'Название',
            'value' => 'Значение',
            'add' => 'Добавить атрибут',
            'translations' => 'Переводы',
        ],
        'placeholders' => [
            'available_stock' => 'Доступный остаток',
        ],
        'filters' => [
            'category' => 'Категория',
            'is_active' => [
                'label' => 'Активность',
                'true' => 'Активные',
                'false' => 'Неактивные',
            ],
        ],
        'images' => [
            'title' => 'Изображения',
            'fields' => [
                'image' => 'Изображение',
                'alt_text' => 'Альтернативный текст',
                'is_primary' => 'Основное изображение',
                'preview' => 'Превью',
                'disk' => 'Диск',
                'sort' => 'Порядок',
                'created_at' => 'Создано',
            ],
            'helper_texts' => [
                'is_primary' => 'Используется как превью товара.',
            ],
            'actions' => [
                'create' => 'Добавить',
                'edit' => 'Редактировать',
                'delete' => 'Удалить',
            ],
            'empty' => [
                'heading' => 'Пока нет изображений',
                'description' => 'Добавьте изображения товара, чтобы увидеть их здесь.',
            ],
        ],
    ],

    'categories' => [
        'fields' => [
            'name' => 'Название',
            'slug' => 'Слаг',
            'parent' => 'Родительская категория',
            'deleted_at' => 'Удалено',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
        ],
    ],

    'vendor' => [
        'fields' => [
            'name' => 'Название',
            'slug' => 'Слаг',
            'description' => 'Описание',

            'deleted_at' => 'Удалено',
            'created_at' => 'Создано',
            'updated_at' => 'Обновлено',
        ],
    ],

    'currencies' => [
        'navigation_group' => 'Настройки',
        'code' => 'Код',
        'rate' => 'Курс',
        'rate_vs_base' => 'Курс (к базовой)',
        'updated' => 'Обновлено',
    ],

    'security' => [
        'two_factor' => [
            'not_initialized' => 'Двухфакторная аутентификация не настроена.',
            'invalid_code' => 'Неверный код двухфакторной аутентификации.',
            'enabled' => 'Двухфакторная аутентификация включена.',
        ],
    ],
];
