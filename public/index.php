<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Presentation\Http\HttpKernel;
use FastRoute\RouteCollector;

// Load container
$container = require __DIR__ . '/../src/Infrastructure/Container.php';

// Routes closure (function)
$routesDefinition = require __DIR__ . '/../src/Presentation/Http/Routes/routes.php';

// Initialize kernel
$kernel = new HttpKernel($container, $routesDefinition);

// Handle request
$kernel->handle();
