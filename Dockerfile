# Stage 1 - Build frontend assets
FROM node:20-alpine AS assets

WORKDIR /app
COPY package*.json webpack.config.js ./
RUN npm ci

COPY assets ./assets
RUN npm run build

# Stage 2 - Composer dependencies
FROM php:8.2-cli AS vendor

RUN apt-get update && apt-get install -y \
    git unzip zip libicu-dev libzip-dev libpq-dev \
    && docker-php-ext-install intl pdo pdo_pgsql pdo_mysql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts

# Stage 3 - Final production image
FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git unzip zip libicu-dev libzip-dev libpq-dev \
    && docker-php-ext-install intl pdo pdo_pgsql pdo_mysql zip \
    && a2enmod rewrite

WORKDIR /var/www/html
COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

ENV APP_ENV=prod
RUN mkdir -p var && chown -R www-data:www-data var

# Configure Apache DocumentRoot
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

EXPOSE 80
CMD ["apache2-foreground"]
