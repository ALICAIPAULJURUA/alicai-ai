#!/usr/bin/env bash
set -e

cd /var/www/html

echo "==> Running composer"
composer install --no-dev --optimize-autoloader --no-interaction --no-progress

echo "==> Caching config..."
php artisan config:cache

echo "==> Caching routes..."
php artisan route:cache

echo "==> Caching views..."
php artisan view:cache

echo "==> Deploy script done."