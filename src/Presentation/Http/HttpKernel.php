<?php

declare(strict_types=1);

namespace App\Presentation\Http;

use App\Presentation\Middleware\AuthMiddleware;
use FastRoute\Dispatcher;
use App\Presentation\Middleware\CorsMiddleware;

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
        $request = [
            'server' => $_SERVER,
            'headers' => getallheaders()
        ];
        //===================CorsMiddleware=====================//
        // This runs the CorsMiddleware: it adds CORS headers. 
        if (isset($this->container[CorsMiddleware::class])) {
            $middleware = $this->container[CorsMiddleware::class];
            if (is_callable($middleware)) {
                $middleware = $middleware();
            }
            $middleware->handle();
        }
        //====================AuthMiddleware======================//
        if (isset($this->container[AuthMiddleware::class])) {
            $middleware = $this->container[AuthMiddleware::class];
            if (is_callable($middleware)) {
                $middleware = $middleware();
            }
            $middleware->handle($request, fn($req) => null);
        }

        // Get HTTP method and URI
        $httpMethod = $_SERVER['REQUEST_METHOD']; // Reads the request’s HTTP method (e.g., GET, POST, PUT, etc.).
        $uri = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)); // Extracts the path part of the URL (e.g., /users/123) and decodes any %20-style encoding.

        // Normalize URI
        $uri = str_starts_with($uri, '/index.php') ? substr($uri, strlen('/index.php')) : $uri; // Strips out /index.php if it’s in the URI (some servers include it if rewrite rules aren’t configured).
        $uri = $uri === '' ? '/' : $uri; // If the URI is empty, treat it as / (the homepage route).

        // Dispatch the request
        // Asks FastRoute’s dispatcher to match the request against routing table (routes.php the one that we are passing from the constructor of this class)
        $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {
            // Dispatcher::NOT_FOUND → no route matched.
            case Dispatcher::NOT_FOUND:
                http_response_code(404);
                echo "404 - Not Found";
                break;

            // Dispatcher::METHOD_NOT_ALLOWED → route exists but doesn’t support this HTTP method.
            case Dispatcher::METHOD_NOT_ALLOWED:
                http_response_code(405);
                echo "405 - Method Not Allowed";
                break;

            // Dispatcher::FOUND → route matched, along with the handler + parameters.
            case Dispatcher::FOUND:
                $handler = $routeInfo[1]; // $handler = either a closure or [ControllerClass, method].
                $vars = $routeInfo[2]; // $vars = extracted route parameters (e.g., {id} → 42).

                // If handler is a closure
                if ($handler instanceof \Closure) {
                    echo $handler(...array_values($vars));
                    break;
                }

                // Otherwise assume [ControllerClass, method]
                [$class, $method] = $handler;
                $controller = $this->container[$class] ?? new $class(); // Instantiate the controller (from DI container if available, otherwise new).

                // For POST, PUT, PATCH requests, also parse JSON body:
                if (in_array($httpMethod, ['POST', 'PUT', 'PATCH'])) {
                    $input = json_decode(file_get_contents('php://input'), true) ?? [];
                    $vars = array_merge($vars, [$input]);
                }

                // Finally, call the controller method:
                echo $controller->$method(...array_values($vars));
                break;
        }
    }
}
