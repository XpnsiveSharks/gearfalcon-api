<?php
     /* CorsMiddleware
     *
     * This middleware handles Cross-Origin Resource Sharing (CORS) for incoming HTTP requests.
     * It ensures that only allowed origins, methods, and headers can access the server resources.
     *
     * Key Features:
     * 1. Validates the origin of incoming requests against allowed origins.
     * 2. Sets CORS headers including:
     *    - Access-Control-Allow-Origin
     *    - Access-Control-Allow-Methods
     *    - Access-Control-Allow-Headers
     *    - Access-Control-Allow-Credentials (optional)
     * 3. Handles preflight OPTIONS requests with a 204 No Content response.
     * 4. Blocks unauthorized origins with a 403 Forbidden response and logs the event.
     *
     * Usage:
     * - Configure the allowed origin(s) via the environment variable `ALLOWED_ORIGIN`.
     *   Multiple origins can be comma-separated, e.g., "http://localhost:3000,https://example.com".
     * - Instantiate the middleware and call the `handle()` method at the start of request handling (HttpKernel).
     *
     * Example:
     * $middleware = new CorsMiddleware();
     * $middleware->handle();
     */
declare(strict_types=1);


namespace App\Presentation\Middleware;

final class CorsMiddleware
{
    private array $allowedMethods;
    private array $allowedHeaders;
    private bool $allowCredentials;
    /**
     * Constructor Parameters:
     * @param array $allowedMethods   List of HTTP methods allowed for CORS (default: GET, POST, PUT, PATCH, DELETE, OPTIONS)
     * @param array $allowedHeaders   List of HTTP headers allowed for CORS (default: Content-Type, Authorization)
     * @param bool  $allowCredentials Whether credentials are allowed (default: true)
     *
     * Notes:
     * - Preflight OPTIONS requests are automatically terminated with 204 status code.
     * - If an origin is not allowed, the request is blocked and a JSON error response is returned.
     */
    public function __construct(
        array $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        array $allowedHeaders = ['Content-Type', 'Authorization', 'X-Client-Version'],
        bool $allowCredentials = true
    ) {
        $this->allowedMethods = $allowedMethods;
        $this->allowedHeaders = $allowedHeaders;
        $this->allowCredentials = $allowCredentials;
    }

    public function handle(): void
    {
        $allowedOrigins = array_map('trim', explode(',', getenv('ALLOWED_ORIGIN') ?: '*'));
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // More permissive CORS for development
        $isDevelopment = getenv('APP_ENV') === 'development';

        if (in_array($origin, $allowedOrigins) || in_array('*', $allowedOrigins) || $isDevelopment || empty($origin)) {
            if (!empty($origin)) {
                header("Access-Control-Allow-Origin: $origin");
            } else {
                header("Access-Control-Allow-Origin: *");
            }
            header('Access-Control-Allow-Methods: ' . implode(', ', $this->allowedMethods));
            header('Access-Control-Allow-Headers: ' . implode(', ', $this->allowedHeaders));
            header('Vary: Origin'); // prevent cache issues

            if ($this->allowCredentials) {
                header('Access-Control-Allow-Credentials: true');
            }
        } else {
            error_log("Blocked CORS request from origin: $origin");
            http_response_code(403);
            exit(json_encode(['error' => 'CORS: Origin not allowed']));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}
