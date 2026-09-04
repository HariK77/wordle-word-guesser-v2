<?php
/**
 * Word Guesser Application - Entry Point
 * Bootstrap the application and handle routing
 */

// Define application root path
define('ROOT_PATH', dirname(dirname(__FILE__)));

// Load configuration and helpers
require_once ROOT_PATH . '/config/app.php';
require_once ROOT_PATH . '/app/Helpers/helpers.php';

// Set up error reporting
if (config('debug')) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', config('logs_path') . '/error.log');
}

// Set timezone
date_default_timezone_set(config('timezone'));

// Start session
if (!isset($_SESSION)) {
    session_start();
}

// Autoloader for application classes
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    if (strpos($class, $prefix) !== 0) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = ROOT_PATH . '/app/' . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Simple routing
try {
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path = str_replace('/word-guesser/public', '', $path);
    $path = trim($path, '/');

    // Route mapping
    $routes = [
        '' => ['App\\Controllers\\HomeController', 'index'],
        'clear' => ['App\\Controllers\\HomeController', 'clear'],
        'stats' => ['App\\Controllers\\HomeController', 'stats'],
        'api/guess' => ['App\\Controllers\\ApiController', 'guess'],
    ];

    if (isset($routes[$path])) {
        $controller = new $routes[$path][0]();
        $method = $routes[$path][1];
        echo $controller->$method();
    } else {
        // Default to home
        $controller = new App\Controllers\HomeController();
        echo $controller->index();
    }
} catch (Exception $e) {
    logger()->error($e->getMessage());
    http_response_code(500);
    if (config('debug')) {
        echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
    } else {
        echo 'An error occurred. Please try again later.';
    }
}
