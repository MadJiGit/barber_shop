#!/bin/bash
set -e

echo "Checking database schema..."
# Run as www-data user to avoid permission issues
su -s /bin/sh www-data -c "php bin/console doctrine:schema:update --force --complete" || echo "Schema already exists"

echo "Starting Apache..."
exec apache2-foreground
