<?php
declare(strict_types=1);

namespace App\Presentation\Http;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use App\Presentation\Http\Middleware\CorsMiddleware;

class HttpKernel
{
    private array $container;
    private Dispatcher $dispatcher;

    public function __construct(array $container, callable $routes)
    {
        $this->container = $container;

        // Setup FastRoute dispatcher
        $this->dispatcher = \FastRoute\simpleDispatcher($routes);
    }

    public function handle(): void
    {
        // Run middleware if exists
        if (isset($this->container[CorsMiddleware::class])) {
            $middleware = $this->container[CorsMiddleware::class];
            if (is_callable($middleware)) {
                $middleware = $middleware();
            }
            $middleware->handle();
        }

        // Get HTTP method and URI
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

        // Remove /index.php prefix if present
        $uri = str_starts_with($uri, '/index.php') ? substr($uri, strlen('/index.php')) : $uri;
        $uri = $uri === '' ? '/' : $uri;

        // Dispatch
        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);

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

                $controller = $this->container[$class] ?? new $class();

                // Pass JSON body for POST requests
                if ($httpMethod === 'POST') {
                    $input = json_decode(file_get_contents('php://input'), true) ?? [];
                    $vars = array_merge($vars, [$input]);
                }

                echo $controller->$method(...array_values($vars));
                break;
        }
    }
}
