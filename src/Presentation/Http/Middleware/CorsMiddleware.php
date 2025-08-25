<?php
declare(strict_types=1);

namespace App\Presentation\Http\Middleware;

final class CorsMiddleware
{
    private array $allowedOrigins;
    private array $allowedMethods;
    private array $allowedHeaders;
    private bool $allowCredentials;

    public function __construct(
        array $allowedOrigins = ['*'],
        array $allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
        array $allowedHeaders = ['Content-Type', 'Authorization'],
        bool $allowCredentials = true
    ) {
        $this->allowedOrigins = $allowedOrigins;
        $this->allowedMethods = $allowedMethods;
        $this->allowedHeaders = $allowedHeaders;
        $this->allowCredentials = $allowCredentials;
    }

    public function handle(): void
    {
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';

        if (in_array($origin, $this->allowedOrigins) || in_array('*', $this->allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
            header('Access-Control-Allow-Methods: ' . implode(', ', $this->allowedMethods));
            header('Access-Control-Allow-Headers: ' . implode(', ', $this->allowedHeaders));
            if ($this->allowCredentials) {
                header('Access-Control-Allow-Credentials: true');
            }
        }

        // Preflight request
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
    }
}
