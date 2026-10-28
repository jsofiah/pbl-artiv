# Stage 1: Build Frontend Assets
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .

# Terima env dari build arg
ARG VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ARG VITE_REVERB_PORT
ARG VITE_REVERB_SCHEME
ENV VITE_REVERB_APP_KEY=$VITE_REVERB_APP_KEY
ENV VITE_REVERB_HOST=$VITE_REVERB_HOST
ENV VITE_REVERB_PORT=$VITE_REVERB_PORT
ENV VITE_REVERB_SCHEME=$VITE_REVERB_SCHEME

RUN npm run build

# Stage 2: Final PHP Image (Nginx + PHP-FPM)
FROM serversideup/php:8.4-fpm-nginx

# Copy app files dengan ownership www-data
COPY --chown=www-data:www-data . .

# Copy built assets dengan ownership www-data
COPY --from=frontend --chown=www-data:www-data /app/public/build /var/www/html/public/build

# Install PHP extensions dan Supervisor sebagai root
USER root
RUN install-php-extensions gd zip xml \
    && apt-get update \
    && apt-get install -y supervisor \
    && rm -rf /var/lib/apt/lists/*

# Salin konfigurasi Supervisor untuk Reverb
COPY ./docker/supervisor/reverb.conf /etc/supervisor/conf.d/reverb.conf

# Salin konfigurasi Nginx untuk proxy WebSocket ke Reverb
COPY ./docker/nginx/reverb.conf /etc/nginx/server-opts.d/reverb.conf

# Kembali ke www-data untuk composer install
USER www-data
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

# Script deploy dengan permission dan ownership yang benar
COPY --chmod=755 --chown=www-data:www-data ./scripts/00-laravel-deploy.sh /etc/entrypoint.d/99-deploy.sh

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr