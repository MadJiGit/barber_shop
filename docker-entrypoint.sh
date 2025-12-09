#!/bin/bash
set -e

echo "Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || echo "Migrations failed or not needed"

echo "Clearing cache..."
php bin/console cache:clear --no-warmup || echo "Cache clear failed"

echo "Starting Apache..."
exec apache2-foreground
