#!/bin/bash

# Install composer dependencies
composer install --no-dev --optimize-autoloader

# Create .env from example if it doesn't exist
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Generate application key if not set
php artisan key:generate --force

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chmod -R 755 storage bootstrap/cache
