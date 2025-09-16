1. Архітектура

- Backend: Laravel 12 (PHP 8.3), PostgreSQL 16, Redis (черги/кеш), Mailpit (dev email), MinIO (S3-сумісне сховище), Meilisearch (1.8) для пошуку/фасетів, Stripe (Payment Intents + Webhook).

- Frontend: React + Vite (TS), shadcn/ui (+Radix), Playwright (E2E).

- Інфра (dev): Docker Compose (app, horizon, nginx, pg, redis, mailpit, minio, meilisearch, node).

- Інфра (prod): multi-stage Dockerfile (PHP-FPM + зібрані assets), Nginx як reverse proxy/static.

- SEO: динамічні <meta> OG/Twitter, <link rel="canonical/prev/next/alternate">, JSON-LD (Product, Breadcrumb), sitemap.xml, robots.txt.

- Аналітика: GA4 з consent-режимом (EU ready), кастомний cookie-перемикач.

- i18n (каркас): префікс у URL /:lang?, <html lang>, cookie lang, Accept-Language хедер до API, hreflang.

- Замовлення/оплата: Order + OrderItems, Stripe Payment Intents, email-нотифікації по статусах (черги).

2. Середовища, запуск, змінні оточення

2.1 Dev-стек (Docker Compose)

Сервіси:
app (php-fpm для artisan, web), horizon (черги), nginx (порт 8080), pg (5432), redis, mailpit (8025), minio (9000/9001), meilisearch (7700), node (vite dev server 5173).

Запуск:
```bash
    docker compose up -d
    docker compose exec app php artisan key:generate
    docker compose exec app php artisan migrate --seed
```

Vite hot reload для Laravel:    
public/hot вказує на http://localhost:5173 (ми виставляли руками при потребі).

2.2 Prod-стек

- docker/php/Dockerfile.prod — multi-stage: збірка фронту (Node), інсталяція composer без dev, копія assets, Laravel optimize (config/route/view cache).  
- docker/nginx/prod.conf — конфіг для PHP-FPM + статика public/build.

2.3 .env (ключові)

```ini
APP_ENV=local|production
APP_URL=http://localhost:8080
APP_KEY=...

DB_CONNECTION=pgsql
DB_HOST=pg
DB_PORT=5432
DB_DATABASE=shop
DB_USERNAME=shop
DB_PASSWORD=shop

REDIS_HOST=redis

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_FROM_ADDRESS="no-reply@example.test"
MAIL_FROM_NAME="Shop"

FILESYSTEM_DISK=s3
AWS_ENDPOINT=http://minio:9000
AWS_USE_PATH_STYLE_ENDPOINT=true
AWS_ACCESS_KEY_ID=minioadmin
AWS_SECRET_ACCESS_KEY=minioadmin
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=shop

MEILISEARCH_HOST=http://meilisearch:7700
MEILI_NO_ANALYTICS=true

STRIPE_SECRET_KEY=sk_test_...
STRIPE_PUBLISHABLE_KEY=pk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...
STRIPE_SUCCESS_URL=http://localhost:5173/checkout/success?session_id={CHECKOUT_SESSION_ID}&order_id={ORDER_ID}
STRIPE_CANCEL_URL=http://localhost:5173/checkout/cancel

# GA4
VITE_GA_ID=G-XXXXXXXXXX
VITE_COOKIE_DEFAULT=granted|denied
```

3) База даних та доменна модель

3.1 Каталог і постачальники
- `products` — id, name, slug, sku, `category_id` (FK на `categories`), `vendor_id` (`nullable` FK на `vendors`, `nullOnDelete`), JSONB `attributes`, `stock`, прайс у двох форматах (`price` decimal(12,2) + `price_cents` для точних розрахунків), `price_old`, `is_active`, агрегації відгуків (`reviews_count`, `rating`), soft deletes/timestamps. Схема охоплює міграції `database/migrations/2025_08_26_131752_create_products_table.php`, `2025_10_01_121000_add_rating_columns_to_products_table.php`, `2025_10_06_000100_add_price_cents_to_products_table.php` та `2025_10_07_000200_add_vendor_id_to_products_table.php`. Рейтинг перебудовується через `App\Models\Product::refreshRating()`.
- `product_images` — медіа-галерея з `product_id`, `url`, `alt`, `is_primary`, `sort`, timestamps (`database/migrations/2025_08_26_131834_create_product_images_table.php`, `2025_08_29_082612_add_is_primary_to_product_images_table.php`).
- `categories` — дерево категорій з `parent_id` (`database/migrations/2025_08_26_131721_create_categories_table.php`).
- `vendors` — відображення продавця на користувача (`user_id` unique, `name`, `slug`, контакти, опис; `database/migrations/2025_10_07_000000_create_vendors_table.php`). Продукти підтягують `vendor` для сторінки продавця (`App\Http\Controllers\Api\ProductController::sellerProducts`).
- `warehouses` — реєстр складів з базовим записом `MAIN` (`database/migrations/2025_10_05_000000_create_warehouses_table.php`), який використовується методами `App\Models\Product` для резервування/звільнення залишків.
- `product_stocks` — залишки по складах (`product_id`, `warehouse_id`, `qty`, `reserved`, `unique` пара) з ініціалізацією зі старого поля `stock` (`database/migrations/2025_10_05_000100_create_product_stock_table.php`).
- `database/seeders/DemoCatalogSeeder.php` — наповнює склад `MAIN` та створює записи у `product_stocks` через `afterCreating` фабрики продуктів.
- `currencies` — курси валют, база береться з `config('shop.currency.base')` і використовується сервісом `App\Services\Currency\CurrencyConverter` (`database/migrations/2025_10_06_000000_create_currencies_table.php`).
- `reviews` — оцінки/коментарі з `status=pending|approved|rejected`, `rating` 1–5 та індексом за `product_id` (`database/migrations/2025_10_01_120000_create_reviews_table.php`, модель `App\Models\Review`).

3.2 Профілі користувачів, бажання та безпека
- `users` — стандартна таблиця Laravel 12 з полями профілю (`database/migrations/0001_01_01_000000_create_users_table.php`).
- `wishlists` — зв'язок `user_id` ↔ `product_id` з унікальним ключем та каскадним видаленням (`database/migrations/2025_09_20_000000_create_wishlists_table.php`).
- `addresses` — збережені адреси доставки/платника (`user_id` nullable, `name`, `city`, `addr`, `postal_code`, `phone`; `database/migrations/2025_09_16_070141_create_addresses_table.php`).
- `two_factor_secrets` — секрети TOTP з `confirmed_at`, 1:1 до користувача (`database/migrations/2025_10_08_000100_create_two_factor_secrets_table.php`, сервіс `App\Services\Auth\TwoFactorService`).

3.3 Кошик, купони та програма лояльності
- `carts` — UUID-ідентифікатор, `user_id`, `status=active|ordered` (`database/migrations/2025_08_26_131903_create_carts_table.php`).
- `cart_items` — позиції кошика з копією ціни на момент додавання (`database/migrations/2025_08_26_131918_create_cart_items_table.php`).
- `coupons` — маркетингові купони (`code`, `type=fixed|percent`, `value`, мін/макс обмеження, usage/per-user ліміти, вікно активності, `meta`; `database/migrations/2025_10_02_000000_create_coupons_table.php`).
- `loyalty_point_transactions` — історія нарахувань/списань балів (`type=earn|redeem|adjustment`, `points`, `amount`, `order_id`, `meta`; `database/migrations/2025_10_02_000100_create_loyalty_point_transactions_table.php`, модель `App\Models\LoyaltyPointTransaction`).
- `carts` мають колонки `coupon_id`, `coupon_code`, `loyalty_points_used` (`database/migrations/2025_10_02_000200_add_discounts_to_carts_table.php`), які синхронізує `App\Services\Carts\CartPricingService`.

3.4 Замовлення і післяпродажний супровід
- `orders` — крім базових полів містить `subtotal`, `discount_total`, `coupon_id`, `coupon_code`, `coupon_discount`, `loyalty_points_used`, `loyalty_points_value`, `loyalty_points_earned` (`database/migrations/2025_10_02_000300_add_discounts_to_orders_table.php`), `shipping_address_id` (`2025_09_16_070146_add_shipping_address_id_to_orders_table.php`), валюту та Stripe-колонки (`currency`, `payment_intent_id`, `payment_status`, `paid_at`; `2025_09_14_155640_stripe_to_orders.php`). Додатково — таймстемпи життєвого циклу (`paid_at`, `shipped_at`, `cancelled_at`, `inventory_committed_at`; `2025_08_27_070140_alter_orders_lifecycle_columns.php`). Статуси описані enum `App\Enums\OrderStatus`.
- `order_items` — `product_id`, `warehouse_id` (`nullable` FK на `warehouses`), `qty`, `price` (`database/migrations/2025_10_05_000200_add_warehouse_id_to_order_items_table.php`).
- `shipments` — відправлення з `status`, `tracking_number`, `shipped_at`, `delivered_at`, `address_id` (`database/migrations/2025_09_16_070144_create_shipments_table.php`).
- `order_status_logs` — журнал переходів статусів (`database/migrations/2025_08_29_144935_create_order_status_logs_table.php`).
- `messages` — чат користувача з менеджерами по замовленню (`order_id`, `user_id`, `body`, `meta`; `database/migrations/2025_10_07_000100_create_messages_table.php`, модель `App\Models\Message`).

4) API (контракти, що використовує фронт)

Маршрути описані в `routes/api.php`. Захищені ендпоінти використовують `auth:sanctum` (`App\Http\Controllers\Api\AuthController`).

4.1 Категорії, пошук та каталог
```bash
GET /api/categories
→ 200: Category[]
```
```bash
GET /api/search/suggestions?q=iphone&limit=8
→ 200: { data: SearchSuggestion[], query, driver }
```
```typescript
GET /api/products?page=1&per_page=12
  &category_id=NUMBER?
  &search=STRING?
  &sort=new|price_asc|price_desc?
  &color[]=red&color[]=blue?
  &size[]=M&size[]=L?
  &min_price=NUMBER?&max_price=NUMBER?
  &with_facets=1?
→ 200: {
  data: Product[],
  current_page, last_page, total,
  facets?: {
    "category_id"?: { [id: string]: number },
    "attrs.color"?: { [val: string]: number },
    "attrs.size"?:  { [val: string]: number },
  }
}
```
- `GET /api/products/facets?...` повертає лише розподіли фасетів (`nbHits`, `driver`, `error?`).
- `GET /api/products/{slug}` — детальна картка товару з вендором/зображеннями.
- `GET /api/products/{id}/reviews` — `{ data, average_rating, reviews_count }`.
- `POST /api/products/{id}/reviews` (auth) — `{ rating: 1..5, text? }` → 201, статус `pending` (`App\Http\Controllers\Api\ReviewController`).
- `GET /api/seller/{vendor}/products` — товари конкретного продавця.
- Для мультивалютності доступний префікс `/api/{currency}/products/...` (див. `Route::group` із `Route::pattern('currency')`).

4.2 Кошик (`App\Http\Controllers\Api\CartController`)
- `GET /api/cart` — повертає або створює активний кошик (сетить cookie `cart_id`).
- `GET /api/cart/{id}` — отримання кошика за UUID.
- `POST /api/cart/{id}/items` — `{ product_id, qty? }` додає/збільшує позицію.
- `PATCH /api/cart/{id}/items/{item}` — `{ qty }` оновлює кількість або видаляє позицію при `qty=0`.
- `DELETE /api/cart/{id}/items/{item}` — видаляє позицію.
- `POST /api/cart/apply-coupon` — `{ cart_id, code? }` застосовує/скидає купон, розрахунок робить `App\Services\Carts\CartPricingService`.
- `POST /api/cart/apply-points` — `{ cart_id, points }`, доступно лише авторизованим; у відповіді приходять блоки `loyalty_points`, `available_points`, `max_redeemable_points`.

4.3 Вішліст (`App\Http\Controllers\Api\WishlistController`, `auth:sanctum`)
- `GET /api/profile/wishlist` — масив обраних товарів (`id`, `slug`, `name`, `price`, `preview_url`).
- `POST /api/profile/wishlist/{product}` — додає товар у вішліст.
- `DELETE /api/profile/wishlist/{product}` — видаляє товар.

4.4 Адреси профілю (`App\Http\Controllers\Api\AddressController`, `auth:sanctum`)
- `GET /api/profile/addresses` — список адрес користувача.
- `POST /api/profile/addresses` — створити адресу.
- `GET /api/profile/addresses/{id}` — переглянути адресу.
- `PATCH /api/profile/addresses/{id}` — часткове оновлення.
- `DELETE /api/profile/addresses/{id}` — видалення.

4.5 Замовлення і повідомлення
- `POST /api/orders` — створює замовлення за `cart_id`, email і адресою доставки; враховує купони, бали, склади (контролер `App\Http\Controllers\Api\OrderController`).
- `GET /api/orders/{number}` — деталі замовлення, також доступно з префіксом `/api/{currency}/orders/{number}`.
- `GET /api/orders/{order}/messages` (auth) — повертає `{ data: Message[] }` (контролер `App\Http\Controllers\Api\OrderMessageController`).
- `POST /api/orders/{order}/messages` (auth) — `{ body }` створює повідомлення, 201 + payload з автором.

4.6 Оплата Stripe (`App\Http\Controllers\Api\PaymentController`)
```yaml
POST /api/payments/intent { number: "ORD-..." }
→ 200: {
  clientSecret: "pi_..._secret_...",
  publishableKey: "pk_test_...",
  order: { number, payment_status, total, currency }
}

POST /api/payment/refresh/{number} { payment_intent?: "pi_..." }
→ 200: { ok: true, status: "paid|new|cancelled", payment_status: "succeeded|..." }

POST /stripe/webhook
→ 200: { ok: true }
```
Вебхук реалізований у `app/Http/Controllers/StripeWebhookController.php`.

4.7 Двофакторна автентифікація (`App\Http\Controllers\Api\TwoFactorController`, `auth:sanctum`)
- `GET /api/profile/two-factor` — статус (`enabled`, `pending`, `confirmed_at`).
- `POST /api/profile/two-factor` — генерує секрет і `otpauth_url`.
- `POST /api/profile/two-factor/confirm` — `{ code }`, підтверджує секрет.
- `DELETE /api/profile/two-factor` — вимикає 2FA.

5) Frontend (структура та ключові елементи)

5.1 Сторінки
- `resources/js/shop/pages/Catalog.tsx` — синхронізує фільтри/пошук з URL (`useQueryParam*`), показує фасети з Meilisearch, оновлює SEO через `<SeoHead />` і `<JsonLd />`.
- `resources/js/shop/pages/Product.tsx` — SEO (OG, Twitter, JSON-LD), "схожі" та "переглянуті" товари, кнопки вішліста/кошика.
- `resources/js/shop/pages/Wishlist.tsx` — відмальовує обране, використовує `useWishlist()` для синхронізації локального стану з `/api/profile/wishlist` та показує помилки (`resources/js/shop/hooks/useWishlist.tsx`).
- `resources/js/shop/pages/OrderConfirmation.tsx` — завантажує замовлення, показує `PayOrder` та чат з продавцем через `OrderChat`.
- `resources/js/shop/pages/Profile.tsx` — головна сторінка профілю з налаштуванням 2FA (`TwoFactorApi`) та навігацією (`ProfileNavigation`).
- `resources/js/shop/pages/ProfileAddresses.tsx` — CRUD адрес через `AddressesApi`.
- `resources/js/shop/pages/ProfileOrders.tsx` — історія замовлень із `OrdersApi.listMine()` (очікує бекенд `/api/profile/orders`).
- `resources/js/shop/pages/ProfilePoints.tsx` — відображає баланс і історію балів через `ProfileApi.fetchPoints()` (очікує `/api/profile/points`).

5.2 Компоненти
- `resources/js/shop/components/MiniCart.tsx` — поповер кошика в хедері.
- `resources/js/shop/components/WishlistButton.tsx`, `WishlistBadge.tsx` — UI для вішліста.
- `resources/js/shop/components/SimilarProducts.tsx`, `RecentlyViewed.tsx` — маркетингові блоки з лоадерами.
- `resources/js/shop/components/SeoHead.tsx` — upsert метатеги/лінки у `<head>`; `resources/js/shop/components/JsonLd.tsx` — інжектує JSON-LD.
- `resources/js/shop/components/PayOrder.tsx` — Stripe Elements (див. §6).
- `resources/js/shop/components/OrderChat.tsx` — фронтовий чат для `/api/orders/{order}/messages`.
- `resources/js/shop/components/ProfileNavigation.tsx` — таби профілю.

5.3 Хуки та клієнти
- `resources/js/shop/hooks/useWishlist.tsx` — керує локальним/віддаленим вішлістом (Sync з `WishlistApi`).
- `resources/js/shop/hooks/useQueryParam*.tsx`, `useDebounce.tsx`, `useDocumentTitle.tsx`, `useHreflangs.tsx` — утиліти для каталогу та SEO.
- `resources/js/shop/api.tsx` — axios-клієнт із `CartApi`, `OrdersApi`, `WishlistApi`, `ProfileApi`, `TwoFactorApi`.

5.4 i18n (каркас)
- Роутер: `/:lang(uk|en)?/*` — опційний префікс.
- `resources/js/shop/i18n/LocaleProvider.tsx` ставить `<html lang>`, cookie та експортує `useLocale()`.
- `resources/js/shop/components/LanguageSwitcher.tsx` — перемикає префікс у URL.
- `resources/js/shop/api.tsx` додає `Accept-Language` до всіх API запитів.
- Серверний middleware `app/Http/Middleware/SetLocaleFromRequest.php` вибирає локаль за префіксом/кукою/Accept-Language.
6) Оплата (Stripe)

6.1 Потік (Payment Intents, Elements)

1) На сторінці замовлення (`resources/js/shop/pages/OrderConfirmation.tsx`) рендеримо `<PayOrder number=... />` (`resources/js/shop/components/PayOrder.tsx`).
2) `PayOrder` викликає `POST /api/payments/intent { number }` (`App\Http\Controllers\Api\PaymentController::intent`), отримує `clientSecret` і `publishableKey`.
3) Stripe Elements/confirm → редірект (якщо 3DS) → повернення на `/order/{number}?payment_intent=pi_...&redirect_status=...`.
4) Фронт викликає `POST /api/payment/refresh/{number}` (`PaymentController::refreshStatus`), щоб підтягнути актуальний статус.
5) Контролер мапить Stripe статус на `App\Enums\OrderStatus`, оновлює `payment_status`, `payment_intent_id`, виставляє `paid_at` і не даунгрейдить вже оплачені/відправлені замовлення.

6.2 Webhook

- `POST /stripe/webhook` обробляє `app/Http/Controllers/StripeWebhookController.php`: перевірка підпису `STRIPE_WEBHOOK_SECRET`, події `payment_intent.*`, ідемпотентне оновлення тих самих полів, що й у `refreshStatus`.

6.3 Email-и

- Черги (`app/Jobs/SendOrderConfirmation.php`, `SendOrderStatusMail.php`, `SendOrderStatusUpdate.php`) шлють `OrderPlacedMail`, `OrderPaidMail`, `OrderShippedMail`, `OrderStatusUpdatedMail`.
- Dev: перегляд у Mailpit (http://localhost:8025).

7) Пошук/Фасети (Meilisearch 1.8)

7.1 Індекс

- Документи формуються через `App\Models\Product::toSearchableArray()` (id, name, price, category_id, attrs.color, attrs.size, slug, preview_url, тощо).
- Facets: `category_id`, `attrs.color`, `attrs.size` — конфігуруються в `App\Http\Controllers\Api\ProductController::index` / `facets`.
- Пошук: `q` = назва; додаткові `filter` за category_id, attrs.color, attrs.size, price range. Каталог на фронті (`resources/js/shop/pages/Catalog.tsx`) очікує такі ж поля.
- Пошукові підказки реалізовані в `App\Http\Controllers\Api\SearchController` (`GET /api/search/suggestions`).

7.2 Важливо (оновлення API Meili 1.8)

- Поле `facetsDistribution` замінене новим синтаксисом (`facets` у запиті).
- `/api/products/facets` повертає лише фасети, щоб уникнути гонок — фронт або запитує каталог з `with_facets=1`, або підтягує фасети окремо.

8) SEO

8.1 Динамічні метатеги

- Компонент `resources/js/shop/components/SeoHead.tsx` upsert-ить:
  - `<title>`, `<meta name="description">`
  - `og:*` (title, description, type, url, image, site_name)
  - `twitter:*` (card, title, description, image)
  - `<link rel="canonical">`
  - `<link rel="prev/next">` для пагінації
  - `<link rel="alternate" hreflang="...">` (через `useHreflangs`)
  - `<meta name="robots">` (за потреби)

8.2 JSON-LD

- `resources/js/shop/components/JsonLd.tsx` інжектує Product + BreadcrumbList на картці товару.
- У каталозі (`Catalog.tsx`) додаємо BreadcrumbList для списків.

8.3 Sitemap/Robots

- `GET /sitemap.xml` генерує `app/Http/Controllers/SitemapController.php` (розділяє index/categories/products, параметри: changefreq, priority, lastmod).
- `public/robots.txt` — мінімальний allow + лінк на sitemap.

9) Аналітика (GA4 + consent)

- `resources/js/shop/ui/analytics.tsx`:
  - `getAnalyticsId()` читає `VITE_GA_ID`.
  - `initAnalyticsOnLoad()` — підключає gtag-скрипт при `consent=granted`.
  - `setConsent('granted'|'denied')`, `updateConsent()` — шлють в GA4 оновлення consent.
  - `openCookiePreferences()` — тригер для UI.
- UI використовує модуль у `resources/js/shop/components/CookieConsent.tsx` та `resources/js/shop/components/Header.tsx`.

    За замовчуванням поведінка керується VITE_COOKIE_DEFAULT (granted або denied). Для ЄС можна ставити denied і показувати банер/кнопку керування.

10) i18n (детально)

- URL-стратегія: `/:lang(uk|en)?/*` — опційний префікс (без префіксу = uk).
- Вибір локалі на бекенді: middleware `app/Http/Middleware/SetLocaleFromRequest.php` (префікс → cookie → Accept-Language).
- Вибір локалі на фронті: `resources/js/shop/i18n/LocaleProvider.tsx` зберігає `<html lang>` та cookie; `resources/js/shop/components/LanguageSwitcher.tsx` міняє префікс і перезавантажує URL.
- API: `resources/js/shop/api.tsx` через axios interceptor додає `Accept-Language: uk|en`.
- Переклади PHP: `resources/lang/{uk,en}/*.php` (поки мінімум).
- Для контенту з БД — наступний етап (JSON-поля або translation-таблиці).

11) Тестування

- Playwright E2E:
- - e2e-flow.spec.tsx: каталог → продукт → кошик → checkout → order confirmation.
- - e2e-facets.spec.tsx: перевірка фасетів, URL, результатів, ціни.
- PHPUnit / Laravel тест-сюїти: Unit/Feature (мінімум підготовлений; розширимо під час hardening).

12) CI/CD

- GitHub Actions
- - php-tests: піднімає Postgres, запускає composer install, міграції, php artisan test.
- - frontend-build-and-tests: npm ci, npm run build, Playwright.
- - docker-build-push: збірка з docker/php/Dockerfile.prod і пуш у GHCR (або Docker Hub).

- Prod Dockerfile: див. §2.2.
- - Secrets: VITE_GA_ID, VITE_SENTRY_DSN (якщо буде), токени реєстру контейнерів.

13) Моніторинг / логування

- Mailpit для dev.
- Sentry (поки не вкл. — швидко додається через @sentry/react та SENTRY_DSN).
- Laravel logs у storage/logs/laravel.log.
- Stripe events: Stripe CLI (stripe listen --forward-to http://host/stripe/webhook) під час dev.

14) Безпека

- CSRF виключено для POST /stripe/webhook (додали в VerifyCsrfToken::except у Laravel 12-стилі, або зареєстрували маршрут поза web middleware).
- CORS MinIO налаштований у docker.
- APP_KEY секретний, у prod — APP_ENV=production, APP_DEBUG=false.
- Валідація інпутів у контролерах/форм реквестах (за потреби розширити).
- Stripe Webhook — strict signature verification.

15) Емейли/черги

- Всі відправки листів — через Jobs (Horizon). При зміні статусу (Observer/Service) штовхаємо:

- - `app/Jobs/SendOrderConfirmation.php` після створення,
- - `app/Jobs/SendOrderStatusMail.php` для Paid/Shipped,
- - `app/Jobs/SendOrderStatusUpdate.php` для інших переходів (email-нотифікації).
- - відновлення кошика — окремий етап (поки не робили).

- Dev: Mailpit UI.

16) Руководство по розробці

16.1 Типові команди
```bash
# міграції
    docker compose exec app php artisan migrate
    
    # сидинг/індексація в Meilisearch (якщо є команди)
    docker compose exec app php artisan scout:import "App\Models\Product"
    
    # очистка кешів (корисно при мета-тегах/конфігах)
    docker compose exec app php artisan optimize:clear
```

16.2 Stripe dev

```bash
    stripe listen --forward-to localhost:8080/stripe/webhook
    # після оплати перевірте в Order: status=paid, payment_status=succeeded
```

16.3 Виробничий реліз (спрощено)

```bash
    # CI зібрав образ ghcr.io/ORG/REPO/shop-php:latest
    # на сервері:
    docker compose -f docker-compose.prod.yml pull
    docker compose -f docker-compose.prod.yml up -d --remove-orphans
    docker compose -f docker-compose.prod.yml exec php php artisan migrate --force
```

17) Траблшутінг

- Meilisearch facets: якщо бачите помилки типу Unknown field facetsDistribution — означає старий клієнтський код або не та версія; ми вже перейшли на facets (Meili ≥1.3), тепер ок.
- React hooks order: у ProductPage ми упорядкували hooks (всі useMemo/useEffect вище if (!p) return...), більше не міняти їх порядок умовно.
- MiniCart null: додали null-safe cart?.items ?? [].
- GA4 не завантажується: перевір VITE_GA_ID, VITE_COOKIE_DEFAULT, що Vite бачить нові env (перезапуск node-контейнера).
- Stripe 419 у вебхуку: або CSRF не вимкнено для /stripe/webhook, або URL не співпадає; перевір шлях, secret, підпис.
- Order статус не змінюється: перевір refreshStatus виклик на фронті після редіректу та логи /stripe/webhook.

18) Що далі (після MVP)

- Переклади з БД: зберігати name/description як JSON по мовам або окремі таблиці *_translations. API віддає локалізовані поля за app()->getLocale().
- Повноцінний Cookie Banner (UI) з переліком категорій cookies.
- Sentry (frontend + backend).
- Детальні unit/feature тести бекенду (оплата, стани).
- Кешування/ETag на GET /api/products (HTTP кеш, conditional GET), оптимізація Meili (synonyms/typo tolerance/configurable filters).
- Платіжний sandbox альтернатив (Mollie/Adyen) для ЄС — робиться на базі того ж контракту.
- Завершити бекенд для `/api/profile/orders` та `/api/profile/points` (фронт уже викликає ці ендпоінти).
- Поліпшити синхронізацію вішліста: мердж гостьових списків з акаунтом без дублювання.

19) Дерево ключового коду 

    Дерево ключового коду знаходиться в codebook.pdf





