<?php
// Simple test file
phpinfo();
echo "\n\n=== Laravel Test ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Current Dir: " . __DIR__ . "\n";

// Fix .env to enable debug
if (file_exists(__DIR__.'/.env')) {
    $env = file_get_contents(__DIR__.'/.env');
    if (strpos($env, 'APP_DEBUG=false') !== false || strpos($env, 'APP_DEBUG=production') !== false) {
        $env = str_replace('APP_DEBUG=false', 'APP_DEBUG=true', $env);
        $env = str_replace('APP_DEBUG=production', 'APP_DEBUG=true', $env);
        file_put_contents(__DIR__.'/.env', $env);
        echo "\n✓ Updated .env to enable debug mode\n";
    }
}

echo "Files in root: \n";
print_r(scandir(__DIR__));
echo "\n\n.env exists: " . (file_exists(__DIR__.'/.env') ? 'YES' : 'NO') . "\n";
echo "vendor exists: " . (file_exists(__DIR__.'/vendor/autoload.php') ? 'YES' : 'NO') . "\n";
echo "bootstrap exists: " . (file_exists(__DIR__.'/bootstrap/app.php') ? 'YES' : 'NO') . "\n";

// Check environment variables
echo "\n\n=== Environment Variables ===\n";
echo "APP_KEY: " . (getenv('APP_KEY') ? 'SET' : 'NOT SET') . "\n";
echo "DB_HOST: " . (getenv('DB_HOST') ? getenv('DB_HOST') : 'NOT SET') . "\n";

echo "\n\n=== Now visit /index.php - it should show detailed error ===\n";
