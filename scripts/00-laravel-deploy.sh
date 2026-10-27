#!/usr/bin/env bash
set -e

echo "Discovering packages..."
php artisan package:discover --ansi

echo "DB_CONNECTION=[$DB_CONNECTION]"
echo "DB_URL length=${#DB_URL}"
echo "DB_HOST=[$DB_HOST]"

echo "Caching config..."
php artisan config:cache

echo "Caching routes..."
php artisan route:cache

echo "Caching views..."
php artisan view:cache

echo "Running migrations..."
php artisan migrate --force