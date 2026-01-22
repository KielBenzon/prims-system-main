<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

// Enable error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('LARAVEL_START', microtime(true));

// Create .env file from environment variables if it doesn't exist
if (!file_exists(__DIR__.'/.env') && getenv('APP_KEY')) {
    $envContent = "APP_NAME=PRIMS\n";
    $envContent .= "APP_ENV=" . (getenv('APP_ENV') ?: 'production') . "\n";
    $envContent .= "APP_KEY=" . getenv('APP_KEY') . "\n";
    $envContent .= "APP_DEBUG=true\n";  // Enable debug mode temporarily
    $envContent .= "APP_URL=" . (getenv('APP_URL') ?: 'https://prims-church-system.azurewebsites.net') . "\n\n";
    
    $envContent .= "DB_CONNECTION=" . (getenv('DB_CONNECTION') ?: 'pgsql') . "\n";
    $envContent .= "DB_HOST=" . getenv('DB_HOST') . "\n";
    $envContent .= "DB_PORT=" . (getenv('DB_PORT') ?: '5432') . "\n";
    $envContent .= "DB_DATABASE=" . getenv('DB_DATABASE') . "\n";
    $envContent .= "DB_USERNAME=" . getenv('DB_USERNAME') . "\n";
    $envContent .= "DB_PASSWORD=" . getenv('DB_PASSWORD') . "\n\n";
    
    if (getenv('SUPABASE_URL')) {
        $envContent .= "SUPABASE_URL=" . getenv('SUPABASE_URL') . "\n";
        $envContent .= "SUPABASE_ANON_KEY=" . getenv('SUPABASE_ANON_KEY') . "\n\n";
    }
    
    if (getenv('AZURE_COMPUTER_VISION_KEY')) {
        $envContent .= "AZURE_COMPUTER_VISION_KEY=" . getenv('AZURE_COMPUTER_VISION_KEY') . "\n";
        $envContent .= "AZURE_COMPUTER_VISION_ENDPOINT=" . getenv('AZURE_COMPUTER_VISION_ENDPOINT') . "\n\n";
    }
    
    if (getenv('GOOGLE_CLIENT_ID')) {
        $envContent .= "GOOGLE_CLIENT_ID=" . getenv('GOOGLE_CLIENT_ID') . "\n";
        $envContent .= "GOOGLE_CLIENT_SECRET=" . getenv('GOOGLE_CLIENT_SECRET') . "\n\n";
    }
    
    $envContent .= "SESSION_DRIVER=file\n";
    $envContent .= "CACHE_STORE=file\n";
    $envContent .= "QUEUE_CONNECTION=sync\n";
    
    file_put_contents(__DIR__.'/.env', $envContent);
    chmod(__DIR__.'/.env', 0644);
}

// Register the Composer autoloader
require __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Force HTTPS scheme detection for Azure proxy
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['SERVER_PORT'] = 443;
}

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
