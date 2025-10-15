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
            // The middleware is called here. It will modify the $request array by reference.
            // The rest of the application logic (routing and controller dispatch) is wrapped
            // inside the $next callable, ensuring it only runs if the middleware allows it.
            $middleware->handle($request, function(array $processedRequest) {
                // Get HTTP method and URI
                $httpMethod = $processedRequest['server']['REQUEST_METHOD'];
                $uri = rawurldecode(parse_url($processedRequest['server']['REQUEST_URI'], PHP_URL_PATH));

                // Normalize URI
                $uri = str_starts_with($uri, '/index.php') ? substr($uri, strlen('/index.php')) : $uri;
                $uri = $uri === '' ? '/' : $uri;

                // Dispatch the request
                $routeInfo = $this->dispatcher->dispatch($httpMethod, $uri);

                switch ($routeInfo[0]) {
                    case \FastRoute\Dispatcher::NOT_FOUND:
                        http_response_code(404);
                        echo "404 - Not Found";
                        break;

                    case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                        http_response_code(405);
                        echo "405 - Method Not Allowed";
                        break;

                    case \FastRoute\Dispatcher::FOUND:
                        $handler = $routeInfo[1];
                        $vars = $routeInfo[2];

                        if ($handler instanceof \Closure) {
                            echo $handler(...array_values($vars));
                            break;
                        }

                        [$class, $method] = $handler;
                        $controller = $this->container[$class] ?? new $class();

                        // Use the $processedRequest which now contains the 'user' object from the middleware.
                        $args = $processedRequest + $vars;

                        if (in_array($httpMethod, ['POST', 'PUT', 'PATCH'])) {
                            $input = json_decode(file_get_contents('php://input'), true) ?? [];
                            $args = array_merge($args, $input);
                        }

                        echo $controller->$method($args);
                        break;
                }
            }); // End of middleware handle call
            return; // Stop further execution in handle() as middleware chain handles the response.
        }

        // This part will now only be reached if no AuthMiddleware is registered in the container.
        // You might want to add the routing logic here again for that case, or throw an error.
        // For now, we assume AuthMiddleware is always present.
        http_response_code(500);
        echo json_encode(['error' => 'Kernel misconfiguration: No middleware chain was executed.']);
    }
}
