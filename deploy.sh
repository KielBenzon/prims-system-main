#!/bin/bash
set -e

echo "=== Starting Deployment ==="

# Create symbolic links for public assets
echo "Creating symbolic links for assets..."
ln -sf /home/site/wwwroot/public/css /home/site/wwwroot/css 2>/dev/null || true
ln -sf /home/site/wwwroot/public/js /home/site/wwwroot/js 2>/dev/null || true
ln -sf /home/site/wwwroot/public/assets /home/site/wwwroot/assets 2>/dev/null || true
ln -sf /home/site/wwwroot/public/storage /home/site/wwwroot/storage 2>/dev/null || true

# Install composer dependencies
echo "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --no-interaction

# Create .env file from environment variables (Azure doesn't create this automatically)
echo "Creating .env file from environment variables..."
cat > .env << EOF
APP_NAME=PRIMS
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL}

DB_CONNECTION=${DB_CONNECTION:-pgsql}
DB_HOST=${DB_HOST}
DB_PORT=${DB_PORT:-5432}
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

SUPABASE_URL=${SUPABASE_URL}
SUPABASE_ANON_KEY=${SUPABASE_ANON_KEY}

AZURE_COMPUTER_VISION_KEY=${AZURE_COMPUTER_VISION_KEY}
AZURE_COMPUTER_VISION_ENDPOINT=${AZURE_COMPUTER_VISION_ENDPOINT}

GOOGLE_CLIENT_ID=${GOOGLE_CLIENT_ID}
GOOGLE_CLIENT_SECRET=${GOOGLE_CLIENT_SECRET}

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
EOF

# Set permissions BEFORE caching
echo "Setting permissions..."
chmod -R 777 storage bootstrap/cache

# Clear and cache configuration
echo "Caching configuration..."
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== Deployment Complete ==="
