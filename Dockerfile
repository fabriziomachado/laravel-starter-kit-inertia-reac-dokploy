# syntax=docker/dockerfile:1

FROM composer:2 AS php-deps

WORKDIR /app

COPY composer.json composer.lock* ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-progress \
    --no-scripts \
    --ignore-platform-reqs

COPY . .

RUN mkdir -p storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache/data \
    storage/logs \
    bootstrap/cache \
    && rm -f bootstrap/cache/packages.php bootstrap/cache/services.php bootstrap/cache/config.php \
    && cp .env.example .env \
    && php artisan key:generate --force \
    && composer dump-autoload --optimize \
    && php artisan package:discover --ansi \
    && php artisan wayfinder:generate --no-interaction

FROM php-deps AS frontend

COPY --from=oven/bun:alpine /usr/local/bin/bun /usr/local/bin/bun

RUN bun install --frozen-lockfile 2>/dev/null || bun install \
    && bun run build

FROM serversideup/php:8.5-frankenphp AS runtime

ENV PHP_EXTENSIONS="sockets"

WORKDIR /var/www/html

COPY --from=php-deps --chown=www-data:www-data /app/app ./app
COPY --from=php-deps --chown=www-data:www-data /app/bootstrap ./bootstrap
COPY --from=php-deps --chown=www-data:www-data /app/config ./config
COPY --from=php-deps --chown=www-data:www-data /app/database ./database
COPY --from=php-deps --chown=www-data:www-data /app/public ./public
COPY --from=php-deps --chown=www-data:www-data /app/resources ./resources
COPY --from=php-deps --chown=www-data:www-data /app/routes ./routes
COPY --from=php-deps --chown=www-data:www-data /app/storage ./storage
COPY --from=php-deps --chown=www-data:www-data /app/vendor ./vendor
COPY --from=php-deps --chown=www-data:www-data /app/artisan ./artisan
COPY --from=php-deps --chown=www-data:www-data /app/composer.json ./composer.json

COPY --from=frontend --chown=www-data:www-data /app/public/build ./public/build

USER root

RUN mkdir -p storage/framework/sessions \
    storage/framework/views \
    storage/framework/cache/data \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

USER www-data
