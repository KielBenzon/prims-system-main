#!/bin/bash

echo "Running custom startup script..."

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

# Start PHP-FPM and Nginx
echo "Starting services..."
/usr/local/bin/php-fpm -D
nginx -g "daemon off;"
