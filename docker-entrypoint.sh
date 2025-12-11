#!/bin/bash
set -e

echo "Running Doctrine migrations..."
su -s /bin/sh www-data -c "php bin/console doctrine:migrations:migrate --no-interaction" || true

echo "Installing bundle assets..."
su -s /bin/sh www-data -c "php bin/console assets:install public --no-interaction" || true

echo "Starting Apache..."
exec apache2-foreground
