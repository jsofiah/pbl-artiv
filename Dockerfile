# Stage 1: Build Frontend Assets
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Stage 2: Final PHP Image (Nginx + PHP-FPM)
FROM serversideup/php:8.4-fpm-nginx

# Copy app files
COPY --chown=www-data:www-data . .

# Copy built assets
COPY --from=frontend --chown=www-data:www-data /app/public/build /var/www/html/public/build

# Install composer sebagai www-data (user default image)
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Script deploy
COPY --chmod=755 --chown=www-data:www-data ./scripts/00-laravel-deploy.sh /etc/entrypoint.d/99-deploy.sh

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr