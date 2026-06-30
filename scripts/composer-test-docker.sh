#!/usr/bin/env bash

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

docker run --rm \
    -v "${ROOT}:/app" \
    -w /app \
    -e COMPOSER_MEMORY_LIMIT=-1 \
    -e XDEBUG_MODE=coverage \
    php:8.5-cli-bookworm \
    bash -lc '
        set -euo pipefail
        export DEBIAN_FRONTEND=noninteractive

        apt-get update -qq
        apt-get install -y -qq git unzip curl libzip-dev libsqlite3-dev libpng-dev libonig-dev libxml2-dev libcurl4-openssl-dev autoconf dpkg-dev file g++ gcc libc-dev make pkg-config re2c > /dev/null

        docker-php-ext-install -j"$(nproc)" zip pdo_sqlite sockets > /dev/null
        pecl install xdebug > /dev/null
        docker-php-ext-enable xdebug > /dev/null

        cat > /usr/local/etc/php/conf.d/testing.ini <<EOF
memory_limit=512M
xdebug.mode=coverage
EOF

        curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
        curl -fsSL https://bun.sh/install | bash > /dev/null
        export PATH="/root/.bun/bin:${PATH}"

        curl -fsSL https://deb.nodesource.com/setup_24.x | bash - > /dev/null
        apt-get install -y -qq nodejs > /dev/null

        git config --global --add safe.directory /app

        composer install --no-interaction --prefer-dist --optimize-autoloader --no-progress
        cp .env.example .env
        php artisan key:generate --force
        bun install
        bun run build
        bunx playwright install --with-deps chromium

        composer test
    '
