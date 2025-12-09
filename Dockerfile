# Stage 1 - Composer dependencies
FROM composer:2 AS vendor

WORKDIR /app
COPY . .
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Stage 2 - PHP + Apache
FROM php:8.2-apache

# Install system dependencies and PHP extensions (PostgreSQL-ready)
RUN apt-get update && apt-get install -y \
    git unzip zip libicu-dev libzip-dev libpng-dev libpq-dev \
    && docker-php-ext-install intl pdo pdo_pgsql pgsql zip opcache \
    && a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy project files and vendor dependencies from previous stage
COPY . .
COPY --from=vendor /app/vendor ./vendor

# Environment settings
ENV APP_ENV=prod
ENV COMPOSER_ALLOW_SUPERUSER=1

# Set correct permissions for Symfony writable directories
RUN mkdir -p var && chown -R www-data:www-data var

# Expose port 80 for Apache
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]