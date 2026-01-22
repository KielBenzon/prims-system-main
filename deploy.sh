#!/bin/bash

# Copy custom nginx configuration
if [ -f nginx/default ]; then
    echo "Copying nginx configuration..."
    cp nginx/default /etc/nginx/sites-available/default
    service nginx reload || true
fi

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
