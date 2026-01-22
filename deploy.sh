#!/bin/bash

# Create symbolic links for public assets
echo "Creating symbolic links for assets..."
ln -sf /home/site/wwwroot/public/css /home/site/wwwroot/css 2>/dev/null || true
ln -sf /home/site/wwwroot/public/js /home/site/wwwroot/js 2>/dev/null || true
ln -sf /home/site/wwwroot/public/assets /home/site/wwwroot/assets 2>/dev/null || true
ln -sf /home/site/wwwroot/public/storage /home/site/wwwroot/storage 2>/dev/null || true

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
