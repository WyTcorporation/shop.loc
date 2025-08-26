FROM php:8.3-fpm-alpine

# Базові пакети
RUN apk add --no-cache git curl zip unzip icu-dev oniguruma-dev libpq-dev libzip-dev libpng-dev freetype-dev libjpeg-turbo-dev \
    && docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j$(nproc) intl mbstring pdo pdo_pgsql zip gd bcmath opcache pcntl

# Redis
RUN pecl install redis && docker-php-ext-enable redis

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Налаштування PHP
COPY docker/supervisord.conf /etc/supervisord.conf

WORKDIR /var/www/html
