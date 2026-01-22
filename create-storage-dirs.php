<?php
// Create missing Laravel storage directories

$dirs = [
    __DIR__.'/storage/framework',
    __DIR__.'/storage/framework/cache',
    __DIR__.'/storage/framework/cache/data',
    __DIR__.'/storage/framework/sessions',
    __DIR__.'/storage/framework/views',
    __DIR__.'/storage/framework/testing',
];

echo "<h1>Creating Missing Storage Directories</h1>";

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0777, true)) {
            chmod($dir, 0777);
            echo "<p style='color: green;'>✓ Created: $dir</p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to create: $dir</p>";
        }
    } else {
        echo "<p style='color: blue;'>Already exists: $dir</p>";
    }
}

// Also create .gitignore files so they persist in Git
$gitignores = [
    __DIR__.'/storage/framework/cache/.gitignore' => "*\n!data/\n!.gitignore\n",
    __DIR__.'/storage/framework/cache/data/.gitignore' => "*\n!.gitignore\n",
    __DIR__.'/storage/framework/sessions/.gitignore' => "*\n!.gitignore\n",
    __DIR__.'/storage/framework/views/.gitignore' => "*\n!.gitignore\n",
    __DIR__.'/storage/framework/testing/.gitignore' => "*\n!.gitignore\n",
];

echo "<hr><h2>Creating .gitignore files</h2>";
foreach ($gitignores as $file => $content) {
    if (file_put_contents($file, $content)) {
        echo "<p style='color: green;'>✓ Created: $file</p>";
    }
}

echo "<hr>";
echo "<h2 style='color: green;'>✓ Done! Now visit /index.php - Laravel should work!</h2>";
echo "<p><a href='/index.php' style='font-size: 20px; padding: 10px 20px; background: green; color: white; text-decoration: none; border-radius: 5px;'>→ Visit Laravel App</a></p>";
