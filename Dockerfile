FROM richarvey/nginx-php-fpm:latest

COPY . .

ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr
ENV COMPOSER_ALLOW_SUPERUSER 1

COPY ./docker/supervisor/reverb.conf /etc/supervisor/conf.d/reverb.conf

COPY ./docker/nginx/reverb.conf /etc/nginx/conf.d/reverb.conf

CMD ["/start.sh"]