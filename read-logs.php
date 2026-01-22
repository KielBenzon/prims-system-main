<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h1>Laravel Error Log Reader</h1>";
echo "<hr>";

// Show .env contents
echo "<h2>.env File Contents:</h2>";
if (file_exists(__DIR__.'/.env')) {
    echo "<pre>" . htmlspecialchars(file_get_contents(__DIR__.'/.env')) . "</pre>";
} else {
    echo "<p style='color: red;'>NO .env FILE!</p>";
}

echo "<hr>";

// Show Laravel log
$logFile = __DIR__.'/storage/logs/laravel.log';
echo "<h2>Laravel Log (last 100 lines):</h2>";
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    $lastLines = array_slice($lines, -100);
    echo "<pre style='background: #000; color: #0f0; padding: 20px;'>" . htmlspecialchars(implode("\n", $lastLines)) . "</pre>";
} else {
    echo "<p style='color: red;'>NO LOG FILE at: " . $logFile . "</p>";
    echo "<p>Checking if storage/logs directory exists...</p>";
    if (is_dir(__DIR__.'/storage/logs')) {
        echo "<p>Directory exists. Files in it:</p>";
        echo "<pre>" . print_r(scandir(__DIR__.'/storage/logs'), true) . "</pre>";
    } else {
        echo "<p style='color: red;'>Directory does NOT exist!</p>";
    }
}

echo "<hr>";

// Check permissions
echo "<h2>Storage Permissions:</h2>";
$dirs = [
    'storage',
    'storage/logs',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'bootstrap/cache',
];

foreach ($dirs as $dir) {
    $fullPath = __DIR__.'/'.$dir;
    if (is_dir($fullPath)) {
        $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
        $writable = is_writable($fullPath) ? '✓ WRITABLE' : '✗ NOT WRITABLE';
        echo "<p>$dir: $perms $writable</p>";
    } else {
        echo "<p style='color: red;'>$dir: DOES NOT EXIST</p>";
    }
}
