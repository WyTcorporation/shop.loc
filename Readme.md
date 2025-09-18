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

2.4 DemoCatalogSeeder (демо-каталог)

- `php artisan db:seed --class=DemoCatalogSeeder`
- Сидер перед створенням даних очищає таблиці `categories`, `vendors`, `products`, `product_images` без тригерів подій, а також скидає Meilisearch-індекс продуктів, тому його можна запускати повторно без дублювання.
- Після повторного запуску API `/api/products?with_facets=1` і фронтовий список категорій показують назви категорій, а не резервний формат `#ID`.

3) База даних та доменна модель

3.1 Основні таблиці (скорочено)

products
- id, slug, name, price (decimal(10,2)), stock (int), category_id (FK), preview_url (nullable), attrs (json?) — залежно від вашої реалізації атрибутів.
- Індекс у Meilisearch (див. §7).

product_images
- id, product_id, url, alt, is_primary (bool).

categories
- id, name, slug, parent_id (nullable).

orders
- id, number (унікальний), user_id (nullable), email,
- status (enum App\Enums\OrderStatus: new|paid|shipped|cancelled),
- total (decimal(10,2)), currency (string, напр. EUR|UAH),
- shipping_address (json), billing_address (json),
- Stripe: payment_intent_id (string nullable), payment_status (string nullable), paid_at (datetime nullable),
shipped_at, cancelled_at (datetime nullable), inventory_committed_at (datetime nullable),
timestamps.

Важливо: у фінальному варіанті немає колонки payment_provider. Ми її не використовуємо.

order_items
- id, order_id, product_id, qty (int), price (decimal(10,2)).

order_status_logs
- id, order_id, from (string), to (string), note (nullable), created_at.

3.2 Enum статусів

App\Enums\OrderStatus:
- New, Paid, Shipped, Cancelled
- Перехідні правила у Order::canTransitionTo()/transitionTo().

3.3 Модель Order (важливі моменти)
- $fillable: email, status, total, shipping_address, billing_address, note, number, currency, payment_intent_id, payment_status, paid_at
- $casts: status як enum, total decimal:2, всі *_at як datetime, адреси як array.
- Генерація номера замовлення: ORD-YYYYMMDD-<RANDOM16>.
- Логіка переходів (paid/shipped/cancelled) коректно віднімає/повертає stock.
- recalculateTotal() підсумовує qty*price із order_items.

4) API (контракти, що використовує фронт)

4.1 Категорії
```bash
    GET /api/categories
    → 200: Category[]
```

4.2 Товари (каталог)
```typescript
    GET /api/products?page=1&per_page=12
      &category_id=NUMBER?
      &search=STRING?
      &sort=new|price_asc|price_desc?
      &color[]=red&color[]=blue?  (або color=red,blue — залежить від вашої реалізації)
      &size[]=M&size[]=L?
      &min_price=NUMBER?&max_price=NUMBER?
      &with_facets=1?
    → 200: {
      data: Product[],
      current_page, last_page, total, ...
      facets?: {
        "category_id"?: { [id: string]: number },
        "attrs.color"?: { [val: string]: number },
        "attrs.size"?:  { [val: string]: number },
      }
    }
```

- Сортування: new (за датою створення/ідентифікатором у зворотньому порядку), price_asc/desc.
- Фільтри: взаємопоєднувані.
- Фасети: формуються в Meilisearch (див. §7).

4.3 Фасет-тільки endpoint (якщо використовується окремо)
```pgsql
    GET /api/products/facets?search=&category_id=&color=red,blue&size=M,L
    → 200: { facets, nbHits, driver, error? }
```

4.4 Кошик   
(у нас hook useCart() — бекенд має мати типові REST-ендпоїнти: /api/cart GET/POST/PUT/DELETE).
Фактичні назви/схема залежать від вашої реалізації (ми інтегрували «як було»).

4.5 Замовлення  
```bash
    GET /api/orders/{number}
    → 200: Order + items + product (name, slug, preview_url)
    
    POST /api/orders         
    → 201: { number, ... }
```

4.6 Оплата Stripe
```yaml 
    POST /api/payment/intent
      { number: "ORD-..." }
    → 200: {
      clientSecret: "pi_..._secret_...",
      publishableKey: "pk_test_...",
      order: { number, payment_status, total, currency }
    }
    
    POST /api/payment/refresh/{number}
      { payment_intent?: "pi_..." }    # опціонально, якщо є у query після редіректу
    → 200: { ok: true, status: "paid|new|cancelled", payment_status: "succeeded|..." }
    
    POST /stripe/webhook         # Stripe webhooks (без CSRF)
    → 200: { ok: true }
```

5) Frontend (структура та ключові елементи)

5.1 Сторінки

- pages/Catalog.tsx
- - Синхронізація фільтрів/пошуку з URL (useQueryParam*).
- - Фасети: category_id, attrs.color, attrs.size, ціновий діапазон.
- - Сортування: new|price_asc|price_desc.
- - OG/Twitter/Canonical/Prev/Next через <SeoHead />.
- - Breadcrumb JSON-LD <JsonLd />.

- pages/Product.tsx (ProductPage)
- - SEO: <SeoHead> з OG зображенням (pre-rendered og/product/:slug.png), Product + Breadcrumb JSON-LD.
- - Recently viewed (localStorage).
- - «Схожі товари» (за category_id, без поточного).
- - «Вішліст» (localStorage).
- - Безпечний порядок hooks (все деривується через useMemo, early return нижче hooks).

- pages/OrderConfirmation.tsx
- - Завантажує замовлення, рендерить таблицю, GA purchase, якщо не сплачено — <PayOrder number=... />.
- - Після успіху onPaid={() => window.location.reload()}.

5.2 Компоненти

- components/MiniCart.tsx — поповер у хедері з коротким підсумком.
- components/WishlistButton.tsx, components/WishlistBadge.tsx.
- components/SimilarProducts.tsx, components/RecentlyViewed.tsx (з loading / empty states).
- components/SeoHead.tsx — upsert метатеги/лінки у <head> (title, description, OG/Twitter, canonical, prev/next, hreflang, robots).
- components/JsonLd.tsx — інжектує <script type="application/ld+json">.

5.3 Хуки

- useQueryParam, useQueryParamNumber, useQueryParamEnum — двосторонній зв’язок із URL.
- useDebounce — debounce пошуку.
- useDocumentTitle — синх title.
- useHreflangs — build alternate-посилання для поточної сторінки.

5.4 i18n (каркас)

- Роутер: /:lang(uk|en)?/* — опційний префікс.
- i18n/LocaleProvider — ставить <html lang>, cookie lang, expose useLocale().
- components/LanguageSwitcher — перемикає префікс у URL.
- Axios interceptor (в api.tsx) додає Accept-Language до всіх API запитів.
- Серверний middleware встановлює app()->setLocale() за префіксом/кукою/Accept-Language.

6) Оплата (Stripe)

6.1 Потік (Payment Intents, Elements)

1) На сторінці замовлення (OrderConfirmation) рендеримо <PayOrder number=... />.
2) PayOrder викликає POST /api/payment/intent { number }, отримує clientSecret і pk.
3) Stripe Elements/confirm → редірект (якщо 3DS) → повернення на /order/{number}?payment_intent=pi_...&...&redirect_status=succeeded.
4) Фронт викликає POST /api/payment/refresh/{number} (ми додали метод refreshStatus).
5) Бекенд тягне PI і мапить Stripe(status) → OrderStatus:

- succeeded → Paid (+ paid_at),
- processing|requires_payment_method → New,
- canceled → Cancelled.

    Також зберігає payment_status (= raw stripe status) та payment_intent_id.

6.2 Webhook

- POST /stripe/webhook (без CSRF; роут під web або окремий) — перевірка підпису STRIPE_WEBHOOK_SECRET.
- Обробляємо події payment_intent.* і ідемпотентно оновлюємо ті самі поля, що й у refreshStatus.
- Важливо: ми не знижуємо статус із Paid назад.

6.3 Email-и

- Після статусів (на стороні Observer / Jobs) шлемо листи:
- - OrderPlacedMail (після формування),
- - OrderPaidMail (після оплати),
- - інші статусні (OrderShippedMail, OrderStatusUpdatedMail).
- Dev: перегляд у Mailpit (http://localhost:8025).

7) Пошук/Фасети (Meilisearch 1.8)

7.1 Індекс

- Документи: продукти (id, name, price, category_id, attrs.color, attrs.size, slug, preview_url, інше).
- Facets: category_id, attrs.color, attrs.size.
- Пошук: q = назва; додаткові filter за category_id, attrs.color, attrs.size, price range.

7.2 Важливо (оновлення API Meili 1.8)

- Поле facetsDistribution замінене новим синтаксисом (facets у запиті).
- Ми переробили бек (/api/products/facets) і фронт, щоб уникнути «гонок»: або отримуємо фасети разом з каталогом (with_facets=1), або окремим запитом.

8) SEO

8.1 Динамічні метатеги

- Компонент SeoHead upsert-ить:
- - <title>, <meta name="description">
- - og:* (title, description, type, url, image, site_name)
- - twitter:* (card, title, description, image)
- - <link rel="canonical">
- - <link rel="prev/next"> для пагінації
- - <link rel="alternate" hreflang="..."> (через hreflangs проп)
- - <meta name="robots"> (за потреби)

8.2 JSON-LD

- Product + BreadcrumbList на картці товару.
- У каталозі — BreadcrumbList.

8.3 Sitemap/Robots

- GET /sitemap.xml — динамічний (головна, категорії ?category_id=..., продукти).

    Параметри: changefreq, priority, lastmod.

- public/robots.txt — мінімальний allow, sitemap посилання.

9) Аналітика (GA4 + consent)

- ui/analytics.ts (або подібний модуль):
- getAnalyticsId() читає VITE_GA_ID.
- initAnalyticsOnLoad() — підключає gtag скрипт при consent=granted.
- setConsent('granted'|'denied'), updateConsent() — шле в GA4 consent update.
- openCookiePreferences() — кидає кастомну подію (ми її слухаємо десь у UI).

    За замовчуванням поведінка керується VITE_COOKIE_DEFAULT (granted або denied). Для ЄС можна ставити denied і показувати банер/кнопку керування.

10) i18n (детально)

- URL-стратегія: /:lang(uk|en)?/* — опційний префікс (без префіксу = uk).
- Вибір локалі на бекенді: middleware SetLocaleFromRequest (префікс → cookie → Accept-Language).
- Вибір локалі на фронті: LocaleProvider зберігає <html lang>, cookie lang; LanguageSwitcher міняє префікс і перезавантажує URL.
- API: interceptor додає Accept-Language: uk|en.
- Переклади PHP: resources/lang/{en,uk,ru,pt}/*.php (повний набір базових файлів Laravel і shop.php).
  - Нові або змінені ключі спочатку додаємо в `resources/lang/en`, після чого синхронізуємо переклади для `uk`, `ru` та `pt`.
  - Для Blade-шаблонів і листів використовуємо структуровані ключі з `shop.php` (підрозділи `common`, `orders`, `auth`, `security`).
  - Після оновлень запускаємо `php artisan test --filter=LocalizationTest`, щоб переконатися у наявності перекладів і fallback'у на англійську.
  - Fallback локаль конфігурується через `APP_FALLBACK_LOCALE` у `.env` (за замовчуванням `en`).
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

- - SendOrderConfirmation після створення,
- - SendOrderStatusUpdate для Paid/Shipped/Cancelled,
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
- Профіль користувача / історія замовлень.
- Wishlist серверний (авторизовані акаунти).

19) Дерево ключового коду 

    Дерево ключового коду знаходиться в codebook.pdf





