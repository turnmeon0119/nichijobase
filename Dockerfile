FROM php:8.4-cli

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    git unzip libzip-dev libpng-dev libonig-dev libicu-dev libsqlite3-dev \
 && docker-php-ext-install pdo_mysql pdo_sqlite mbstring zip intl \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts --optimize-autoloader

COPY . .

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
RUN composer dump-autoload --optimize --no-dev

CMD ["./scripts/render-start.sh"]
