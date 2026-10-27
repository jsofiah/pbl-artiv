#!/usr/bin/env bash
set -e

echo "Fixing permissions..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

echo "Discovering packages..."
php artisan package:discover --ansi

echo "Caching config..."
php artisan config:cache

echo "Caching routes..."
php artisan route:cache

echo "Caching views..."
php artisan view:cache

echo "Running migrations..."
php artisan migrate --force