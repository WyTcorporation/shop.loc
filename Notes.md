composer global require laravel/installer
laravel new example-app
cd example-app
npm install && npm run build
php artisan key:generate
php artisan env:encrypt
php artisan env:encrypt --env=staging
composer run dev
php artisan about
php artisan about --only=environment
php artisan config:show database
composer require filament/filament -W
php artisan filament:install --panels  
docker compose exec app php artisan make:filament-user

docker compose build --no-cache app
docker compose up -d --build
docker compose down

docker compose exec app composer create-project laravel/laravel .
docker compose exec app composer require laravel/scout meilisearch/meilisearch-php filament/filament laravel/horizon predis/predis nunomaduro/larastan pestphp/pest --dev laravel/pint --dev
make key
php -S localhost:9001 # (відкрити MinIO console і створити bucket)
Далі: php artisan horizon:install, php artisan vendor:publish --tag=filament-config.


# шелл у контейнері додатку
docker compose exec app sh
# перезапуск усіх контейнерів
docker compose down && docker compose up -d --build
# тести
docker compose exec app ./vendor/bin/pest
# лінтер коду
docker compose exec app ./vendor/bin/pint
# Larastan (приклад суворості)
docker compose exec app vendor/bin/phpstan analyse --memory-limit=1G --level=max app

php artisan migrate
php artisan key:generate
php artisan optimize
php artisan cache:clear
php artisan route:cache
php artisan view:clear
php artisan config:cache

php artisan make:module Sky/Test --all

Найбільш поширені команди Artisan:
php artisan list: Відображає список усіх доступних команд Artisan.
php artisan make:model <ModelName>: Створює новий клас моделі Eloquent.
php artisan make:controller <ControllerName>: Створює новий клас контролера.
php artisan make:migration <MigrationName>: Створює новий файл міграції для роботи з базою даних.
php artisan migrate: Запускає всі міграції бази даних.
php artisan migrate:rollback: Відкочує останні міграції.
php artisan Tinker: Відкриває інтерактивну консоль PHP (REPL) для тестування та роботи з кодом у вашому застосунку.
php artisan inspire: Виводить надихаючу цитату.

Команди для створення компонентів:
php artisan make:channel: Створює новий клас каналу.
php artisan make:command: Створює нову кастомну команду Artisan.
php artisan make:event: Створює нову подію.
php artisan make:exception: Створює нове користувацьке виключення.
php artisan make:factory: Створює фабрику моделі для генерації тестових даних.
php artisan make:job: Створює новий клас завдання (job).
php artisan make:listener: Створює новий слухач події.
php artisan make:mail: Створює новий клас для надсилання електронних листів.
php artisan make:middleware: Створює новий middleware.
php artisan make:notification: Створює нове повідомлення.
php artisan make:observer: Створює новий спостерігач (observer).
php artisan make:policy: Створює нову політику.
php artisan make:provider: Створює новий сервіс-провайдер.
php artisan make:request: Створює новий клас запиту.
php artisan make:resource: Створює новий ресурс.
php artisan make:rule: Створює нове правило валідації.
php artisan make:seeder: Створює новий сидер для заповнення бази даних.
php artisan make:test: Створює новий тест.

docker compose exec app php artisan make:model Category -m
docker compose exec app php artisan make:seeder DemoCatalogSeeder
docker compose exec app php artisan make:factory CategoryFactory --model=Category

docker compose exec app php artisan config:clear
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan migrate:fresh --seed

docker compose exec app php artisan scout:import "App\Models\Product"

docker compose exec app php artisan make:filament-resource Product --generate
docker compose exec app php artisan make:filament-resource Category --generate
docker compose exec app php artisan make:filament-resource Order --generate

docker compose exec app php artisan make:filament-resource Product --generate --panel=mine
docker compose exec app php artisan make:filament-resource Category --generate --panel=mine
docker compose exec app php artisan make:filament-resource Order --generate --panel=mine

docker compose exec app composer dump-autoload
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan optimize:clear
docker compose exec app php artisan filament:clear
docker compose exec app php artisan migrate --pretend

docker compose exec app sh -lc "wget -qO- http://meilisearch:7700/health"
docker compose exec app php artisan route:list | grep -i mine || true

php artisan route:list | grep filament -i || true
docker compose exec app sh -lc "mkdir -p storage/logs bootstrap/cache && chmod -R 777 storage bootstrap/cache"

docker compose exec app sh -lc "tail -n 100 storage/logs/laravel.log"

php artisan make:mail OrderPlacedMail --markdown=emails.orders.placed
php artisan make:job SendOrderConfirmation
php artisan make:listener MergeGuestCart
docker compose exec app php artisan make:test OrderFlowTest --pest

docker compose exec app php artisan make:filament-relation-manager Products Images --panel=mine

# run test
docker compose exec app php artisan test -vvv
docker compose exec app ./vendor/bin/pest -q

docker compose exec app php artisan storage:link

mc alias set local http://minio:9000 minioadmin minioadmin
mc anonymous set-json local/media <<<'{"Version":"2012-10-17","Statement":[{"Effect":"Allow","Principal":{"AWS":["*"]},"Action":["s3:GetBucketLocation","s3:ListBucket"],"Resource":["arn:aws:s3:::media"]},{"Effect":"Allow","Principal":{"AWS":["*"]},"Action":["s3:GetObject"],"Resource":["arn:aws:s3:::media/*"]}]}'
mc admin config set local api cors allow-origin='http://localhost:8080' allow-headers='*' allow-methods='GET,PUT,POST,DELETE,HEAD,OPTIONS'
mc admin service restart local

mc anonymous set download local/media

mc --disable-pager alias set local http://minio:9000 minioadmin minioadmin --api s3v4
mc --disable-pager mb -p local/media || true
cat >/tmp/cors.json <<'JSON'
[
{
"AllowedOrigin": ["http://localhost:8080"],
"AllowedMethod": ["GET", "PUT", "POST", "DELETE", "HEAD", "OPTIONS"],
"AllowedHeader": ["*"],
"ExposeHeader": ["ETag","x-amz-request-id","x-amz-version-id"],
"MaxAgeSeconds": 3000
}
]
JSON

mc --disable-pager admin cors set local/media /tmp/cors.json
mc --disable-pager admin cors info local/media
!!!
mc --disable-pager anonymous set download local/media
mc anonymous set download local/media

docker compose exec app php artisan tinker

docker compose exec app php artisan optimize:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan route:clear

Searchable
docker compose exec app php artisan optimize:clear
# опційно повна реіндексація:
docker compose exec app php artisan scout:flush "App\Models\Product"
docker compose exec app php artisan scout:import "App\Models\Product"


docker compose exec app php artisan tinker --execute="use App\Models\Order; Order::factory()->count(2)->create(['status'=>'new']); Order::factory()->count(1)->create(['status'=>'paid']); Order::factory()->count(1)->create(['status'=>'shipped']);"

docker compose exec app php artisan storage:link
docker compose exec app ln -s ../storage/app/public public/storage


# FRONT

# 1) Прибити hot і старий білд (на випадок сміття)
docker compose exec app sh -lc "rm -f public/hot && rm -rf public/build/*"

docker compose exec app sh -lc 'printf "%s" "http://localhost:5173" > public/hot'
docker compose exec app sh -lc 'php artisan optimize:clear'


# 2) Перезібрати/перезапустити node після змін у compose/vite
docker compose up -d --build node
docker compose logs -f node
docker compose exec app sh -lc 'printf "%s" "http://localhost:5173" > public/hot'

const URL = process.env.E2E_BASE_URL ?? 'http://localhost:8080';

npx playwright test --reporter=html; npx playwright show-report
npx playwright test --config=playwright.config.tsx
npx playwright test --config=playwright.config.tsx -g "similar & recently viewed"

docker compose up -d --force-recreate --no-deps node

http://localhost:8080
— бек

http://localhost:5173
— Vite

stripe listen --forward-to localhost:8080/stripe/webhook
stripe listen --forward-to http://localhost:8080/stripe/webhook

Хочеш — далі можу:
I18n каркас (switcher, URL стратегія, lang у <html>, hreflang).
CI/CD (build, тест, deploy), prod Dockerfile.

4242 4242 4242 4242 - good

4000 0000 0000 0002 - bad


План розширення проєкту
Бекенд (Laravel + PostgreSQL)
Локалізація даних

Впровадити mcamara/laravel-localization для маршрутів.

Додати JSONB‑поля або translation‑таблиці для products, categories, brands.

Повертати з API локалізовані назви/описи.

Мультивалютність

Завести таблицю валют і курси оновлення (через CRON/API).

Зберігати ціни в базовій валюті, повертати конвертовану в API.

Додати поле currency в замовлення, підтримку VAT.

Користувач та профіль

Таблиці addresses, user_measurements, loyalty_points.

Ендпоінти профілю: перегляд/оновлення даних, список замовлень.

Інтеграція Spatie Permissions (ролі: admin, manager, warehouse, accountant, vendor).

Wishlist серверний

Таблиця wishlists(user_id, product_id), API GET/POST/DELETE.

Міграція з localStorage на користувацький акаунт.

Відгуки та Q&A

Таблиця reviews з модерацією.

Таблиця questions для Q&A на сторінках товару.

Notifications/Jobs для сповіщення менеджера.

Купони й акції

Таблиці coupons, promotions.

Сервіси перевірки валідності й застосування знижки в кошику/замовленні.

Адмін‑панель (Filament) для управління промо.

Доставка та відправлення

Таблиця shipments(order_id, carrier, tracking_no, status, shipped_at).

API інтеграції з перевізниками (вебхуки або scheduled polling).

Оновлення статусів у замовленні та логування order_status_logs.

Інвентар та склади

Таблиця warehouses, inventory(product_id, warehouse_id, qty, reserved).

Сервіси резервування при оплаті, списання при відправці.

Фонові jobs для синхронізації.

Програма лояльності

Сервіс нарахування/списання бонусів.

Таблиця points_transactions.

Відображення балансу в API.

Мультивендорність

Таблиця vendors, products.vendor_id.

Розбиття замовлення на підзамовлення по продавцях.

Кастомні ролі та доступи для продавців.

Повідомлення/чат

Таблиця tickets / messages з прив’язкою до замовлення чи товару.

Реалізувати REST API + (опційно) WebSockets для реального часу.

Тестування та безпека

PHPUnit/Pest покриття основних сценаріїв (checkout, оплата, відгуки).

Перевірка CSRF, валідацій, rate‑limit API.

Фронтенд (React + Vite)
Повна i18n

Локалізований роутинг /ua, /en, мовний перемикач.

Витяг перекладів з API для назв/описів.

Підтримка hreflang, <html lang>.

Мультивалютність

Перемикач валют, зберігання вибору (cookie/localStorage).

Відображення цін у вибраній валюті, конвертація на клієнті.

Форматування з урахуванням локалі.

Особистий кабінет

Сторінки: профіль, адреси, історія замовлень, бонуси, налаштування.

Форма оновлення даних, зміна пароля, 2FA.

Компоненти відстеження статусу замовлення та доставки.

Серверний wishlist

Синхронізація з бекендом, показ heart‑статусу в каталозі/картці.

Можливість перенести локальний список після логіну.

Відгуки та Q&A

Компоненти перегляду/додавання відгуків з модераційним статусом.

Блок запитань‑відповідей з пагінацією.

Рейтингова зіркова шкала, підрахунок середнього рейтингу.

Купони/акції

Поле вводу промокоду в кошику й checkout.

Банери акцій, сторінка “Акції” з отриманням даних через API.

Логіка умов безкоштовної доставки, “додайте X для знижки”.

Checkout 2.0

Покроковий/one‑page checkout з валідацією.

Відображення доступних способів доставки та їх вартості.

Інтеграція Stripe Elements/redirect, показ результату.

Відстеження замовлення

UI таймлайн статусів, кнопка “Track shipment” (посилання/вбудований віджет).

Повідомлення користувача про зміну статусу (Toast/WebPush).

Порівняння товарів

Локальний або акаунтовий список для порівняння.

Табличне відображення характеристик, підсвітка відмінностей.

Бонусна програма

Блок “Використати X балів” у кошику.

Відображення балансу в хедері/кабінеті.

Пуш‑сповіщення про нарахування.

Мультивендорний UX

Стрічка продавця в картці товару, рейтинг, сторінка “Магазин продавця”.

Фільтр за продавцем в каталозі.

Повідомлення продавцю та відгуки про продавця.

Маркетингові та CMS‑сторінки

Шаблони для сторінок “Про нас”, “Доставка/Оплата”, FAQ.

Інтеграція CMS‑даних (через API) й відображення банерів, новин.

Порядок впровадження
Фаза 1 – Основи: локалізація даних, мультивалютність, профілі користувачів, серверний wishlist.

Фаза 2 – Контент і маркетинг: відгуки, Q&A, купони, промо‑банери, SEO‑покращення.

Фаза 3 – Логістика й платежі: доставка, мультивендорність, бонусна програма, порівняння товарів.

Фаза 4 – CRM/чат і аналітика: повідомлення, Dashboard аналітики, інтеграція Sentry/GA, додаткові тестові сценарії.

Такий поетапний план дозволить поступово розширювати проєкт, не ламаючи існуючий функціонал і забезпечуючи зростання можливостей у парі бекенд–фронтенд.
