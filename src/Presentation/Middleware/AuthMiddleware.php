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
    public function handle(array $request, callable $next)
    {
        $uri = $request['server']['REQUEST_URI']; // grabbing the current URL path from the request.

        // Only protect specific dashboard routes
        if (
            str_starts_with($uri, '/admin') ||
            str_starts_with($uri, '/technician') ||
            str_starts_with($uri, '/user')
        ) {
            $headers = $request['headers'];

            // Reject if Authorization header is missing
            if (empty($headers['Authorization'])) {
                http_response_code(401);
                return json_encode(['error' => 'Missing Authorization header']);
            }

            // Header must be in "Bearer <token>" format
            if (!preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
                http_response_code(401);
                return json_encode(['error' => 'Invalid Authorization format']);
            }

            $token = $matches[1];

            try {
                // Decode and verify JWT with secret key
                $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));

                // Attach decoded token payload to request
                $request['user'] = (array)$decoded;

            } catch (ExpiredException $e) {
                // Token is expired
                http_response_code(401);
                return json_encode(['error' => 'Token expired']);
            } catch (SignatureInvalidException $e) {
                // Token signature is invalid (wrong key or tampered)
                http_response_code(401);
                return json_encode(['error' => 'Invalid token signature']);
            } catch (UnexpectedValueException $e) {
                // Token could not be parsed/decoded
                http_response_code(401);
                return json_encode(['error' => 'Invalid token']);
            }
        }

        // If route is not protected, or token is valid → continue
        return $next($request);
    }
}
