<?php

declare(strict_types=1);

// Enable error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Set timezone
date_default_timezone_set('Asia/Manila');

// Autoloader (assuming Composer is installed)
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment and container
$container = require_once __DIR__ . '/../src/Infrastructure/Container.php';

// Load routes
$routes = require_once __DIR__ . '/../src/Presentation/Routes/routes.php';

// Create HTTP Kernel
$kernel = new App\Presentation\Http\HttpKernel($container, $routes);

// Handle the request
$kernel->handle();