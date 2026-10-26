#!/usr/bin/env bash
# Runs on the server after the latest code has been pulled (see .github/workflows/deploy.yml).
# PHP_BIN / COMPOSER_BIN can be overridden when cPanel uses a different PHP binary (e.g. /usr/local/bin/ea-php82).
set -euo pipefail

PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"

cd "$(dirname "$0")"

echo "==> Maintenance mode on"
"$PHP_BIN" artisan down --retry=30 || true
trap '"$PHP_BIN" artisan up' EXIT

echo "==> Composer install"
"$COMPOSER_BIN" install --no-dev --no-interaction --prefer-dist --optimize-autoloader

echo "==> Build front-end assets"
npm ci --no-audit --no-fund
npm run build

echo "==> Migrate database"
"$PHP_BIN" artisan migrate --force

echo "==> Cache config, routes and views"
"$PHP_BIN" artisan storage:link || true
"$PHP_BIN" artisan optimize:clear
"$PHP_BIN" artisan optimize

echo "==> Deploy finished"
