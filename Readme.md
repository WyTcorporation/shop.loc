composer global require laravel/installer
laravel new example-app
cd example-app
npm install && npm run build
php artisan env:encrypt
composer run dev
php artisan about
php artisan about --only=environment
php artisan config:show database
