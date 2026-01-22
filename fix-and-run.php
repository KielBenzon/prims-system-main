<?php
// Fix storage permissions before Laravel boots
$storageDirs = [
    __DIR__.'/storage',
    __DIR__.'/storage/framework',
    __DIR__.'/storage/framework/cache',
    __DIR__.'/storage/framework/sessions',
    __DIR__.'/storage/framework/views',
    __DIR__.'/storage/logs',
    __DIR__.'/bootstrap/cache',
];

foreach ($storageDirs as $dir) {
    if (file_exists($dir)) {
        chmod($dir, 0777);
    }
}

// Delete .env to recreate with debug=true
if (file_exists(__DIR__.'/.env')) {
    unlink(__DIR__.'/.env');
}

// Load main index.php
require __DIR__.'/index.php';
