#!/usr/bin/env bash
set -e

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

echo "Starting Supervisor for Reverb..."
supervisord -c /etc/supervisor/supervisord.conf &