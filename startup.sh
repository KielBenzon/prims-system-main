#!/bin/bash

echo "Running custom startup script..."

# Create required Laravel storage directories
echo "Creating storage framework directories..."
mkdir -p /home/site/wwwroot/storage/framework/cache/data
mkdir -p /home/site/wwwroot/storage/framework/sessions
mkdir -p /home/site/wwwroot/storage/framework/views
mkdir -p /home/site/wwwroot/storage/framework/testing
mkdir -p /home/site/wwwroot/storage/logs

# Set proper permissions
echo "Setting storage permissions..."
chmod -R 777 /home/site/wwwroot/storage
chmod -R 777 /home/site/wwwroot/bootstrap/cache

# Run deployment tasks
bash /home/site/wwwroot/deploy.sh

# Copy custom PHP configuration
echo "Configuring PHP..."
cat > /home/site/php.ini << 'EOF'
expose_php = Off
display_errors = Off
log_errors = On
upload_max_filesize = 20M
post_max_size = 20M
memory_limit = 256M
EOF

# Copy Laravel-optimized Nginx configuration
echo "Configuring Nginx for Laravel routing..."
cp /home/site/wwwroot/nginx.conf /etc/nginx/sites-available/default

echo "=== Startup script complete! ==="
echo "Azure will now start PHP-FPM and Nginx automatically."
