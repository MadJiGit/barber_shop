# Stage 1 - Composer dependencies
FROM php:8.2-cli AS vendor

RUN apt-get update && apt-get install -y \
    git unzip zip libicu-dev libzip-dev libpq-dev \
    && docker-php-ext-install intl pdo pdo_pgsql pdo_mysql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Stage 2 - PHP + Apache
FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git unzip zip libicu-dev libzip-dev libpq-dev \
    && docker-php-ext-install intl pdo pdo_pgsql pdo_mysql zip \
    && a2enmod rewrite

WORKDIR /var/www/html
COPY . .
COPY --from=vendor /app/vendor ./vendor

ENV APP_ENV=prod
RUN mkdir -p var && chown -R www-data:www-data var

EXPOSE 80
CMD ["apache2-foreground"]