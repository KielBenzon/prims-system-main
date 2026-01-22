<?php
// Simple test file
phpinfo();
echo "\n\n=== Laravel Test ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Current Dir: " . __DIR__ . "\n";
echo "Files in root: \n";
print_r(scandir(__DIR__));
echo "\n\n.env exists: " . (file_exists(__DIR__.'/.env') ? 'YES' : 'NO') . "\n";
echo "vendor exists: " . (file_exists(__DIR__.'/vendor/autoload.php') ? 'YES' : 'NO') . "\n";
echo "bootstrap exists: " . (file_exists(__DIR__.'/bootstrap/app.php') ? 'YES' : 'NO') . "\n";

// Check environment variables
echo "\n\n=== Environment Variables ===\n";
echo "APP_KEY: " . (getenv('APP_KEY') ? 'SET' : 'NOT SET') . "\n";
echo "DB_HOST: " . (getenv('DB_HOST') ? getenv('DB_HOST') : 'NOT SET') . "\n";
