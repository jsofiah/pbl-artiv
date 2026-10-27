#!/usr/bin/env bash
set -e

echo "=== DEBUG ENV ==="
echo "DB_CONNECTION=[$DB_CONNECTION]"
echo "DB_URL length=${#DB_URL}"
echo "DB_HOST=[$DB_HOST]"
echo "APP_KEY length=${#APP_KEY}"
env | grep -E '^(DB_|DATABASE_)' | sed -E 's#(://[^:]+:)[^@]+@#\1***@#; s#(PASSWORD=).*#\1***#'
echo "=== END DEBUG ==="

php artisan config:clear
php artisan package:discover --ansi
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force