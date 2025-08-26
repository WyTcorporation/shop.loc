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

docker compose build --no-cache app
docker compose up -d --build

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

