FROM php:8.4-fpm-alpine

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    PHP_OPCACHE_ENABLE=1 \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0 \
    PHP_OPCACHE_MAX_ACCELERATED_FILES=10000 \
    PHP_OPCACHE_MEMORY_CONSUMPTION=128 \
    PHP_OPCACHE_MAX_WASTED_PERCENTAGE=10

RUN apk add --no-cache \
    autoconf \
    bash \
    build-base \
    curl \
    freetype-dev \
    git \
    icu-dev \
    jpeg-dev \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    linux-headers \
    oniguruma-dev \
    unzip \
    zip \
    mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mysqli bcmath gd intl opcache zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/cache/apk/*

COPY --from=composer:2.8.1 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/backend
COPY backend/composer.json backend/composer.lock* ./
RUN composer install --no-interaction --no-progress --prefer-dist --optimize-autoloader --no-scripts

COPY backend/ ./

RUN chmod -R 775 storage bootstrap/cache \
    && chmod +x artisan

EXPOSE 9000
CMD ["php-fpm", "-F"]
