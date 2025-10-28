<?php
declare(strict_types=1);

namespace App\Presentation\Http\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use UnexpectedValueException;

/**
 * AuthMiddleware
 *
 * This middleware checks if a request going to certain "protected" routes
 * (admin, technician, user dashboards) has a valid JWT in the Authorization header.
 * 
 * If the token is valid, it attaches the decoded payload (user info, role, etc.)
 * to the request so controllers can use it.
 * 
 * If the token is missing, expired, or invalid → it stops the request and 
 * returns a 401 Unauthorized JSON response.
 */
class AuthMiddleware
{
    private string $jwtSecret;

    /**
     * @var string[]
     */
    private array $protectedPaths = [
        '/admin',
        '/customers', // Protects /customers/complete-profile
        '/auth/logout',
        '/auth/customer-info',
        '/quotes', // Protects all quote creation/management
        '/jobs', // Protects all job-related endpoints
        // Add other protected paths here
    ];

    public function __construct(string $jwtSecret)
    {
        $this->jwtSecret = $jwtSecret;
    }

    /**
     * Handle the request and enforce JWT authentication for protected routes.
     *
     * @param array    $request  The request data (headers, server, etc.)
     * @param callable $next     The next middleware or controllera
     */
    public function handle(array &$request, callable $next)
    {
        $uri = $request['server']['REQUEST_URI']; // grabbing the current URL path from the request.

        $isProtectedRoute = false;
        foreach ($this->protectedPaths as $path) {
            if (str_starts_with($uri, $path)) {
                $isProtectedRoute = true;
                break;
            }
        }

        if (!$isProtectedRoute) {
            // If the route is not in the protected list, just continue.
            return $next($request);
        }

        // --- Authentication Logic for Protected Routes ---
        $headers = $request['headers'];

        if (empty($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Missing Authorization header']);
            return; // Stop execution
        }

        if (!preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid Authorization format']);
            return; // Stop execution
        }

        $token = $matches[1];

        try {
            $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));

            // Fetch the full User model from the database
            $user = \App\Infrastructure\Models\User::find($decoded->sub);

            if (!$user) {
                throw new \Exception('User not found for the provided token.');
            }

            // Attach the Eloquent User object to the request
            $request['user'] = $user;

        } catch (\Exception $e) {
            // Catches ExpiredException, SignatureInvalidException, and our custom one
            http_response_code(401);
            // Use echo and return to stop execution, as this is a middleware
            echo json_encode(['error' => 'Authentication failed: ' . $e->getMessage()]);
            return; // Stop execution
        }

        // If the token is valid, the user object is attached to the request.
        // Now, call the next handler (which will eventually be the controller)
        // to continue processing the request.
        return $next($request);
    }
}
