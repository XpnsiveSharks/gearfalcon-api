<?php
declare(strict_types=1);

// 1. Autoload
require __DIR__ . '/../vendor/autoload.php';

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;


// 2. Setup dispatcher
$dispatcher = FastRoute\simpleDispatcher(require __DIR__ . '/../config/routes.php');

// 3. Get HTTP method and URI
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string
$uri = rawurldecode(parse_url($uri, PHP_URL_PATH));

// Remove leading /index.php if accidentally present
if (strpos($uri, '/index.php') === 0) {
    $uri = substr($uri, strlen('/index.php'));
}
if ($uri === '') {
    $uri = '/';
}

// 4. Dispatch
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo "404 - Not Found";
        break;

    case Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        echo "405 - Method Not Allowed";
        break;

    case Dispatcher::FOUND:
        [$class, $method] = $routeInfo[1];
        $vars = $routeInfo[2];

        // Dependency Injection (DI) ready: controllers can receive constructor dependencies
        $controller = new $class();
        echo $controller->$method(...array_values($vars));
        break;

        // // Use container to resolve controller
        // $controller = $container->get($class);
        // echo $controller->$method(...array_values($vars));
        // break;

   

}
