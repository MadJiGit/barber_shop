#!/bin/bash
set -e

echo "Checking database schema..."
# Try to create schema if it doesn't exist (first deployment)
php bin/console doctrine:schema:update --force --complete || echo "Schema already exists"

echo "Clearing cache..."
php bin/console cache:clear --no-warmup || echo "Cache clear failed"

echo "Starting Apache..."
exec apache2-foreground
