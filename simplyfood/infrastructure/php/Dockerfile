FROM php:8.2-fpm-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
    git unzip libzip-dev libpng-dev libonig-dev libicu-dev libpq-dev \
    && docker-php-ext-install pdo_mysql bcmath intl zip pcntl opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/backend

COPY www.conf /usr/local/etc/php-fpm.d/zz-www.conf
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && mkdir -p /var/www/backend/storage /var/www/backend/bootstrap/cache \
    && chown -R www-data:www-data /var/www/backend

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
