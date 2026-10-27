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

# Install composer dependencies saat build, tanpa script (karena APP_KEY belum ada)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Script deploy hanya untuk cache & migrate (cepat)
COPY --chmod=755 ./scripts/00-laravel-deploy.sh /etc/entrypoint.d/99-deploy.sh

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr