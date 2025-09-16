<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

// Load environment variables - ADD THIS SECTION
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..', '.env');
    $dotenv->load();
} else {
    // Log error if .env file is missing
    error_log("WARNING: .env file not found at: " . __DIR__ . '/../.env');
}

use App\Presentation\Http\HttpKernel;
use FastRoute\RouteCollector;

// Load container
$container = require __DIR__ . '/../src/Infrastructure/Container.php';

/*
 * Routes are not registered in the container because they are not services.
 * - Routes define application rules (URL → controller mapping).
 * - The container's responsibility is only to build and supply dependencies.
 */
// Routes closure (function)
$routesDefinition = require __DIR__ . '/../src/Presentation/Routes/routes.php';

// Initialize kernel
$kernel = new HttpKernel($container, $routesDefinition);

// Handle request
$kernel->handle();