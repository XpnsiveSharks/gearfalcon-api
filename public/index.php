<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Presentation\Http\HttpKernel;

// Load container
$container = require __DIR__ . '/../src/Infrastructure/Container.php';

// Routes closure
$routes = require __DIR__ . '/../src/Presentation/Http/Routes/routes.php';

// Initialize and handle request
$kernel = new HttpKernel($container, $routes);
$kernel->handle();
