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

# Jalankan deploy script via entrypoint S6
COPY ./scripts/00-laravel-deploy.sh /etc/entrypoint.d/99-deploy.sh
COPY --chmod=755 ./scripts/00-laravel-deploy.sh /etc/entrypoint.d/99-deploy.sh

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr

COPY ./docker/nginx/reverb.conf /etc/nginx/server-opts.d/reverb.conf