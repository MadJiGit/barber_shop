# Stage 1 - Composer dependencies
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Stage 2 - PHP + Apache
FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git unzip zip libicu-dev libzip-dev libpng-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip opcache

RUN a2enmod rewrite

WORKDIR /var/www/html

COPY . .
COPY --from=vendor /app/vendor ./vendor

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN chown -R www-data:www-data /var/www/html/var

EXPOSE 80
CMD ["apache2-foreground"]