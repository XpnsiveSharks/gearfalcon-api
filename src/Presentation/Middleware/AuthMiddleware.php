<?php
declare(strict_types=1);

namespace App\Presentation\Middleware;

use App\Infrastructure\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use UnexpectedValueException;

class AuthMiddleware
{
    private string $jwtSecret;

    public function __construct(string $jwtSecret)
    {
        $this->jwtSecret = $jwtSecret;
    }

    public function handle(array $request, callable $next)
    {
        $debug = [];
        $uri = $request['server']['REQUEST_URI']; // grabbing the current URL path from the request.
        $debug['uri'] = $uri;

        // Only protect specific dashboard routes
        if (
            str_starts_with($uri, '/admin') ||
            str_starts_with($uri, '/technician') ||
            str_starts_with($uri, '/user') ||
            str_starts_with($uri, '/auth/customer-info') ||
            str_starts_with($uri, '/customers')
        ) {
            $debug['route'] = 'protected';
            $headers = getallheaders();
            $debug['headers'] = $headers;

            // Reject if Authorization header is missing
            if (empty($headers['Authorization'])) {
                $debug['error'] = 'Missing Authorization header';
                http_response_code(401);
                echo json_encode($debug);
                return;
            }

            // Header must be in "Bearer <token>" format
            if (!preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
                $debug['error'] = 'Invalid Authorization format';
                http_response_code(401);
                echo json_encode($debug);
                return;
            }

            $token = $matches[1];
            $debug['token'] = $token;

            try {
                // Decode and verify JWT with secret key
                $decoded = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
                $debug['decoded'] = (array)$decoded;

                // Find the user from the database and attach to the request
                $user = User::find($decoded->sub);
                $debug['user'] = $user ? $user->toArray() : null;

                if (!$user) {
                    $debug['error'] = 'User not found in DB';
                    http_response_code(401);
                    echo json_encode($debug);
                    return;
                }

                $request['user'] = $user;

            } catch (ExpiredException $e) {
                $debug['error'] = 'Token expired';
                http_response_code(401);
                echo json_encode($debug);
                return;
            } catch (SignatureInvalidException $e) {
                $debug['error'] = 'Invalid token signature';
                http_response_code(401);
                echo json_encode($debug);
                return;
            } catch (UnexpectedValueException $e) {
                $debug['error'] = 'Invalid token';
                http_response_code(401);
                echo json_encode($debug);
                return;
            }
        } else {
            $debug['route'] = 'public';
        }

        $request['debug'] = $debug;

        // If route is not protected, or token is valid → continue
        return $next($request);
    }
}