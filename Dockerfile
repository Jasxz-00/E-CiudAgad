# E-CiudAgad production image (Render.com / any Docker host)
# Base: PHP 8.3 + Nginx + PHP-FPM, Laravel-ready (S6 process manager)
FROM serversideup/php:8.3-fpm-nginx

WORKDIR /var/www/html

USER root

RUN install-php-extensions pdo_mysql gd zip intl bcmath exif pcntl

COPY --chown=www-data:www-data . .

RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache \
    && composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader \
    && npm ci --ignore-scripts \
    && npm run build \
    && php artisan storage:link

USER www-data
