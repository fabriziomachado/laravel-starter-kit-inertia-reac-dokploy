#!/usr/bin/env bash

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
IMAGE="${TEST_IMAGE:-laravel-starter-kit-ci:local}"
TEST_TARGET="${1:-test}"

if ! docker image inspect "${IMAGE}" >/dev/null 2>&1; then
    echo "Building CI image ${IMAGE} (one-time, ~1 min)..."
    docker build -f "${ROOT}/Dockerfile.ci" -t "${IMAGE}" "${ROOT}"
fi

run_in_ci() {
    docker run --rm \
        --user root \
        -v "${ROOT}:/app" \
        -v laravel-starter-kit-composer-cache:/tmp/composer-cache \
        -v laravel-starter-kit-bun-cache:/root/.bun/install/cache \
        -v laravel-starter-kit-playwright-cache:/root/.cache/ms-playwright \
        -e COMPOSER_CACHE_DIR=/tmp/composer-cache \
        -e COMPOSER_MEMORY_LIMIT=-1 \
        -e XDEBUG_MODE=coverage \
        -w /app \
        "${IMAGE}" \
        bash -lc "$1"
}

case "${TEST_TARGET}" in
    test)
        run_in_ci '
            set -euo pipefail
            export PATH="/root/.bun/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"

            git config --global --add safe.directory /app

            if [ ! -f vendor/autoload.php ]; then
                composer install --no-interaction --prefer-dist --optimize-autoloader --no-progress
            fi

            if [ ! -f .env ]; then
                cp .env.example .env
                php artisan key:generate --force
            fi

            if [ ! -d node_modules ]; then
                bun install
            fi

            if [ ! -f public/build/manifest.json ]; then
                bun run build
            fi

            bun x playwright install --with-deps chromium

            composer test
        '
        ;;
    unit)
        run_in_ci '
            set -euo pipefail
            git config --global --add safe.directory /app
            [ -f vendor/autoload.php ] || composer install --no-interaction --prefer-dist --optimize-autoloader --no-progress
            [ -f .env ] || { cp .env.example .env && php artisan key:generate --force; }
            XDEBUG_MODE=coverage ./vendor/bin/pest --parallel --coverage --exactly=100.0 --exclude-testsuite Browser
        '
        ;;
    types)
        run_in_ci '
            set -euo pipefail
            export PATH="/root/.bun/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"
            git config --global --add safe.directory /app
            [ -f vendor/autoload.php ] || composer install --no-interaction --prefer-dist --optimize-autoloader --no-progress
            [ -f .env ] || { cp .env.example .env && php artisan key:generate --force; }
            [ -d node_modules ] || bun install
            composer test:types
        '
        ;;
    lint)
        run_in_ci '
            set -euo pipefail
            export PATH="/root/.bun/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin"
            git config --global --add safe.directory /app
            [ -f vendor/autoload.php ] || composer install --no-interaction --prefer-dist --optimize-autoloader --no-progress
            [ -d node_modules ] || bun install
            composer test:lint
        '
        ;;
    rebuild-image)
        docker build -f "${ROOT}/Dockerfile.ci" -t "${IMAGE}" "${ROOT}"
        ;;
    *)
        echo "Usage: $0 [test|unit|types|lint|rebuild-image]"
        exit 1
        ;;
esac
