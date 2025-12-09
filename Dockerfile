# Stage 1 - Composer dependencies
FROM php:8.2-cli AS vendor

# Install system dependencies and PHP extensions for composer stage
RUN apt-get update && apt-get install -y \
    git unzip zip libicu-dev libzip-dev libpq-dev \
    && docker-php-ext-install intl pdo pdo_pgsql zip

# Install Composer manually
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Stage 2 - PHP + Apache
FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git unzip zip libicu-dev libzip-dev libpng-dev libpq-dev \
    && docker-php-ext-install intl pdo pdo_pgsql pgsql zip opcache \
    && a2enmod rewrite

WORKDIR /var/www/html
COPY . .
COPY --from=vendor /app/vendor ./vendor

ENV APP_ENV=prod
ENV COMPOSER_ALLOW_SUPERUSER=1

RUN mkdir -p var && chown -R www-data:www-data var

EXPOSE 80
CMD ["apache2-foreground"]