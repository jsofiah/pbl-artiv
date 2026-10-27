# Stage 1: Build Frontend Assets
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Stage 2: Final PHP Image (Nginx + PHP-FPM)
FROM serversideup/php:8.4-fpm-nginx

COPY . .

COPY --from=frontend /app/public/build /var/www/html/public/build

# Install composer dependencies saat build, bukan saat runtime
USER root
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
    && chown -R www-data:www-data /var/www/html/vendor /var/www/html/storage /var/www/html/bootstrap/cache
USER www-data

# Hanya jalankan cache & migrate saat runtime (cepat, tidak butuh health check)
COPY --chmod=755 ./scripts/00-laravel-deploy.sh /etc/entrypoint.d/99-deploy.sh

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr

COPY ./docker/nginx/reverb.conf /etc/nginx/server-opts.d/reverb.conf