<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo "<h1>Laravel Bootstrap Debug</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Current Dir: " . __DIR__ . "</p>";
echo "<hr>";

try {
    echo "<p>Step 1: Checking vendor/autoload.php...</p>";
    if (!file_exists(__DIR__.'/vendor/autoload.php')) {
        die("ERROR: vendor/autoload.php not found!");
    }
    echo "<p>✓ vendor/autoload.php exists</p>";
    
    echo "<p>Step 2: Loading composer autoloader...</p>";
    require __DIR__.'/vendor/autoload.php';
    echo "<p>✓ Autoloader loaded</p>";
    
    echo "<p>Step 3: Checking bootstrap/app.php...</p>";
    if (!file_exists(__DIR__.'/bootstrap/app.php')) {
        die("ERROR: bootstrap/app.php not found!");
    }
    echo "<p>✓ bootstrap/app.php exists</p>";
    
    echo "<p>Step 4: Loading Laravel application...</p>";
    $app = require_once __DIR__.'/bootstrap/app.php';
    echo "<p>✓ Laravel app loaded</p>";
    echo "<p>App class: " . get_class($app) . "</p>";
    
    echo "<p>Step 5: Creating HTTP Kernel...</p>";
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "<p>✓ Kernel created</p>";
    echo "<p>Kernel class: " . get_class($kernel) . "</p>";
    
    echo "<p>Step 6: Handling request...</p>";
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    echo "<p>✓ Request handled</p>";
    echo "<p>Response status: " . $response->getStatusCode() . "</p>";
    
    echo "<hr>";
    echo "<h2>SUCCESS! Laravel is working. Sending response...</h2>";
    
    $response->send();
    $kernel->terminate($request, $response);
    
} catch (\Exception $e) {
    echo "<hr>";
    echo "<h2 style='color: red;'>ERROR CAUGHT:</h2>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
} catch (\Throwable $e) {
    echo "<hr>";
    echo "<h2 style='color: red;'>FATAL ERROR:</h2>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>File:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
    echo "<h3>Stack Trace:</h3>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}
