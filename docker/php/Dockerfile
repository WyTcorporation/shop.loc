FROM php:8.3-fpm-alpine

# Base libs for PHP extensions (runtime)
RUN apk add --no-cache \
    git curl zip unzip \
    icu-dev oniguruma-dev libpq-dev libzip-dev \
    libpng-dev freetype-dev libjpeg-turbo-dev

# Build deps for pecl (phpize, autoconf, make, etc.)
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS

# GD, intl, mbstring, postgres, zip, bcmath, opcache, pcntl
RUN docker-php-ext-configure gd --with-jpeg --with-freetype \
 && docker-php-ext-install -j"$(nproc)" intl mbstring pdo pdo_pgsql zip gd bcmath opcache pcntl

# Redis extension via pecl
RUN pecl install redis \
 && docker-php-ext-enable redis

# Clean up build deps to slim the image
RUN apk del .build-deps

# Composer binary
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
