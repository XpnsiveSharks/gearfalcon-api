<?php

namespace App\Presentation\Controllers;

use App\Application\Services\AuthService;
use App\Application\Services\UserRegistrationService;
use App\Application\Services\EmailVerificationService;
use App\Application\Exceptions\InvalidCredentialsException;
use App\Infrastructure\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use UnexpectedValueException;

class AuthController
{
    private AuthService $authService;
    private UserRegistrationService $userRegistrationService;
    private EmailVerificationService $verificationService;
    private string $jwtSecret;
    private string $jwtRefreshSecret;

    public function __construct(
        AuthService $authService,
        UserRegistrationService $userRegistrationService,
        EmailVerificationService $verificationService
    ) {
        $this->authService = $authService;
        $this->userRegistrationService = $userRegistrationService;
        $this->verificationService = $verificationService;
        $this->jwtSecret = $_ENV['JWT_SECRET'] ?? 'your-access-secret-key';
        $this->jwtRefreshSecret = $_ENV['JWT_REFRESH_SECRET'] ?? 'your-refresh-secret-key';
    }

    private function jsonResponse(array $data, int $statusCode = 200): string
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        return json_encode($data);
    }

    public static function setCorsHeaders(): void
    {
        // Docker-compatible CORS configuration
        $allowedOrigins = [
            'http://localhost:3000',           // Browser access
            'http://frontend:3000',           // Container-to-container
            'http://127.0.0.1:3000',          // Alternative localhost
            'https://localhost:3000',          // HTTPS localhost
            'https://frontend:3000',          // HTTPS container
            // Postman origins
            'https://web.postman.co',          // Postman web
            'http://localhost:3000',           // Postman local
            'null',                            // No origin (mobile apps, etc.)
        ];

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // Allow all origins in development or if origin is in allowed list
        if (getenv('APP_ENV') === 'development' || in_array($origin, $allowedOrigins, true) || empty($origin)) {
            if (!empty($origin)) {
                header("Access-Control-Allow-Origin: $origin");
            } else {
                header("Access-Control-Allow-Origin: *");
            }
        }

        // Essential CORS headers for cookie support
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, Accept');
        header('Access-Control-Max-Age: 86400'); // 24 hours preflight cache
    }
    public function login(array $request): string
    {
        $email = $request['email'] ?? '';
        $password = $request['password'] ?? '';

        if (empty($email) || empty($password)) {
            return $this->jsonResponse(['error' => 'Email and password are required'], 422);
        }

        try {
            $user = $this->authService->login($email, $password);

            // If user is not verified, resend code and block token issuance
            if (!(bool)$user->is_verified) {
                try {
                    $this->verificationService->resendVerificationCode($user->email);
                } catch (\Exception $e) {
                    // Log but still return a helpful message
                    error_log('Failed to resend verification code: ' . $e->getMessage());
                }

                return $this->jsonResponse([
                    'error' => 'Email not verified. A new verification code has been sent to your email.'
                ], 403);
            }

            // Generate access token
            $accessToken = $this->generateAccessToken($user);

            // Generate refresh token
            $refreshToken = $this->generateRefreshToken($user);

            // Set both tokens as HTTP-only cookies (for direct backend calls)
            $this->setAccessTokenCookie($accessToken);
            $this->setRefreshTokenCookie($refreshToken);

            // Calculate expiration time
            $expiresIn = (int)(getenv('JWT_TTL_SECONDS') ?: 900);

            // ✅ RETURN access_token in response body for Next.js middleware to set as cookie
            // This allows the Next.js API route to create HTTP-only cookies on the frontend domain
            return $this->jsonResponse([
                'success' => true,
                'access_token' => $accessToken,      // ← Added for frontend
                'refresh_token' => $refreshToken,    // ← Added for frontend
                'expires_in' => $expiresIn,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'is_verified' => $user->is_verified
                ]
            ]);
        } catch (InvalidCredentialsException $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 401);
        }
    }


    public function register(array $request): string
    {
        try {
            // Ensure role is always 'customer' for public registration
            $request['role'] = 'customer';
            
            // Step 1: Create user account (unverified)
            $user = $this->userRegistrationService->registerUser($request);

            // Step 2: Send verification email
            $this->verificationService->sendVerificationCode($user->email);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Registration successful. Please check your email for verification code.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_verified' => false
                ],
                'next_step' => 'verify_email'
            ], 201);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function verifyEmail(array $request): string
    {
        $email = $request['email'] ?? '';
        $code  = $request['verification_code'] ?? '';

        if (empty($email) || empty($code)) {
            return $this->jsonResponse(['error' => 'Email and verification code are required'], 422);
        }

        try {
            // Step 3 & 4: Verify code and activate user account
            $verified = $this->verificationService->verifyEmail($email, $code);

            if (!$verified) {
                return $this->jsonResponse(['error' => 'Invalid verification code'], 400);
            }

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Email verified successfully! You can now login to your account.'
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function resendVerificationCode(array $request): string
    {
        $email = $request['email'] ?? '';

        if (empty($email)) {
            return $this->jsonResponse(['error' => 'Email is required'], 422);
        }

        try {
            $this->verificationService->resendVerificationCode($email);

            return $this->jsonResponse([
                'success' => true,
                'message' => 'Verification code sent successfully. Please check your email.'
            ]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function logout(array $request): string
    {
        $user = $request['user'] ?? null;

        if (!$user instanceof User) {
            return $this->jsonResponse(['error' => 'User not authenticated'], 401);
        }

        $this->authService->logout($user);

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Logged out successfully'
        ]);
    }

    /**
     * POST /auth/refresh
     *
     * Refreshes access token using httpOnly refresh token cookie
     * Works with Docker networking and proper CORS configuration
     */
    public function refresh(array $request): string
    {
        try {
            // Get refresh token from httpOnly cookie
            $refreshToken = $_COOKIE['refresh_token'] ?? '';

            if (empty($refreshToken)) {
                return $this->jsonResponse(['error' => 'No refresh token provided'], 401);
            }

            // Verify refresh token
            $decoded = $this->verifyRefreshToken($refreshToken);
            if (!$decoded) {
                $this->clearRefreshTokenCookie();
                return $this->jsonResponse(['error' => 'Invalid or expired refresh token'], 401);
            }

            // Get user from database
            $user = User::find($decoded->sub);
            if (!$user) {
                $this->clearRefreshTokenCookie();
                return $this->jsonResponse(['error' => 'User not found'], 401);
            }

            // Generate new access token
            $accessToken = $this->generateAccessToken($user);

            // Set as HTTP-only cookie (NOT in response body!)
            $this->setAccessTokenCookie($accessToken);

            // Return success without exposing token
            return $this->jsonResponse([
                'success' => true,
                'expires_in' => (int)(getenv('JWT_TTL_SECONDS') ?: 900)
            ]);
        } catch (\Exception $e) {
            error_log('Refresh token error: ' . $e->getMessage());
            return $this->jsonResponse(['error' => 'Token refresh failed'], 500);
        }
    }

    private function setAccessTokenCookie(string $token): void
    {
        $isSecure = isset($_SERVER['HTTPS']) ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        $domain = getenv('COOKIE_DOMAIN') ?: '';
        $isProduction = getenv('APP_ENV') === 'production';

        setcookie('accessToken', $token, [
            'expires' => time() + (int)(getenv('JWT_TTL_SECONDS') ?: 900),
            'path' => '/',
            'domain' => $domain,
            'secure' => $isSecure && $isProduction,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }
    private function generateAccessToken(User $user): string
    {
        $issuedAt = time();
        $expiresAt = $issuedAt + (int)(getenv('JWT_TTL_SECONDS') ?: 900); // 15 minutes

        $payload = [
            'sub' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'is_verified' => (bool)$user->is_verified,
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'type' => 'access'
        ];

        return JWT::encode($payload, $this->jwtSecret, 'HS256');
    }

    private function generateRefreshToken(User $user): string
    {
        $issuedAt = time();
        $expiresAt = $issuedAt + (7 * 24 * 3600); // 7 days

        $payload = [
            'sub' => $user->id,
            'email' => $user->email,
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'type' => 'refresh'
        ];

        return JWT::encode($payload, $this->jwtRefreshSecret, 'HS256');
    }

    private function verifyRefreshToken(string $token): ?object
    {
        try {
            return JWT::decode($token, new Key($this->jwtRefreshSecret, 'HS256'));
        } catch (ExpiredException $e) {
            error_log('Refresh token expired: ' . $e->getMessage());
            return null;
        } catch (SignatureInvalidException $e) {
            error_log('Invalid refresh token signature: ' . $e->getMessage());
            return null;
        } catch (UnexpectedValueException $e) {
            error_log('Malformed refresh token: ' . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            error_log('Refresh token verification error: ' . $e->getMessage());
            return null;
        }
    }

    private function setRefreshTokenCookie(string $token): void
    {
        $isSecure = isset($_SERVER['HTTPS']) ||
            (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        $domain = getenv('COOKIE_DOMAIN') ?: '';
        $isProduction = getenv('APP_ENV') === 'production';

        // Docker-compatible cookie settings
        setcookie('refresh_token', $token, [
            'expires' => time() + (7 * 24 * 3600), // 7 days
            'path' => '/',
            'domain' => $domain, // Empty for localhost, set for production
            'secure' => $isSecure && $isProduction, // Only secure in production with HTTPS
            'httponly' => true, // Prevent XSS attacks - crucial security feature
            'samesite' => 'Strict' // CSRF protection
        ]);
    }

    private function clearRefreshTokenCookie(): void
    {
        $domain = getenv('COOKIE_DOMAIN') ?: '';

        setcookie('refresh_token', '', [
            'expires' => time() - 3600, // Expire in the past
            'path' => '/',
            'domain' => $domain,
            'secure' => false,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }

    private function shouldRotateRefreshToken(): bool
    {
        // Rotate refresh token 50% of the time for better security
        return rand(1, 100) <= 50;
    }

    public function getCustomerInfo(array $request): string
    {
        $user = $request['user'] ?? null;

        if (!$user instanceof User) {
            return $this->jsonResponse(['error' => 'User not authenticated'], 401);
        }

        if ($user->role !== 'customer') {
            return $this->jsonResponse(['error' => 'Access denied. Not a customer.'], 403);
        }
        
        // Load the customer relationship to check if a customer profile exists
        $user->load(['customer.addresses']);

        if (!$user->customer) {
            return $this->jsonResponse([
                'error' => 'Customer profile not found. Please complete your registration.'
            ], 404);
        }
        
        $primaryAddress = $user->customer->addresses->first();

        return $this->jsonResponse([
            'success' => true,
            'customer' => [
                'customer_id' => $user->customer->id,
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'contact' => $user->customer->contact,
                'role' => $user->role,
                'is_verified' => (bool)$user->is_verified,
                'company_name' => $user->customer->company_name,
                'address' => $primaryAddress ? [
                    'address_id' => $primaryAddress->id,
                    'house_number' => $primaryAddress->house_number,
                    'street' => $primaryAddress->street,
                    'barangay' => $primaryAddress->barangay,
                    'city' => $primaryAddress->city,
                    'province' => $primaryAddress->province,
                    'region' => $primaryAddress->region,
                    'postal_code' => $primaryAddress->postal_code,
                ] : null
            ]
        ]);
    }

    /**
     * Handles the request to change a user's email address.
     *
     * @param array $request The HTTP request data.
     * @return string JSON response.
     */
    public function changeEmail(array $request): string
    {
        $user = $request['user'] ?? null;
        $userIdFromRoute = $request['id'] ?? null;

        if (!$user instanceof User) {
            return $this->jsonResponse(['error' => 'User not authenticated'], 401);
        }

        // Authorization: Ensure the authenticated user is changing their own email.
        if ($user->id !== $userIdFromRoute) {
            return $this->jsonResponse(['error' => 'Forbidden. You can only change your own email.'], 403);
        }

        $jsonInput = file_get_contents('php://input');
        $data = json_decode($jsonInput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->jsonResponse(['error' => 'Invalid JSON provided'], 400);
        }

        $newEmail = $data['new_email'] ?? null;

        if (empty($newEmail) || !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->jsonResponse(['error' => 'A valid new email address is required.'], 400);
        }

        try {
            $updatedUser = $this->authService->changeEmailAndRequestVerification($user, $newEmail);
            return $this->jsonResponse(['success' => true, 'message' => 'Email change initiated. Please verify your new email address.', 'user' => ['id' => $updatedUser->id, 'email' => $updatedUser->email, 'is_verified' => (bool)$updatedUser->is_verified]]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function forgotPassword(array $request): string
    {
        $email = $request['email'] ?? '';

        if (empty($email)) {
            return $this->jsonResponse(['error' => 'Email is required'], 422);
        }

        try {
            $this->authService->forgotPassword($email);
            return $this->jsonResponse(['success' => true, 'message' => 'Password reset code has been sent to your email.']);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function verifyPasswordReset(array $request): string
    {
        $email = $request['email'] ?? '';
        $code = $request['code'] ?? '';

        if (empty($email) || empty($code)) {
            return $this->jsonResponse(['error' => 'Email and code are required'], 422);
        }

        try {
            $verified = $this->authService->verifyPasswordReset($email, $code);
            if ($verified) {
                return $this->jsonResponse(['success' => true, 'message' => 'Code verified successfully.']);
            } else {
                return $this->jsonResponse(['error' => 'Invalid or expired reset code.'], 400);
            }
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function resetPassword(array $request): string
    {
        $email = $request['email'] ?? '';
        $code = $request['code'] ?? '';
        $password = $request['password'] ?? '';

        if (empty($email) || empty($code) || empty($password)) {
            return $this->jsonResponse(['error' => 'Email, code, and new password are required'], 422);
        }

        try {
            $this->authService->resetPassword($email, $code, $password);
            return $this->jsonResponse(['success' => true, 'message' => 'Password has been reset successfully.']);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }

    public function updateProfile(array $request): string
    {
        $user = $request['user'] ?? null;

        if (!$user instanceof User) {
            return $this->jsonResponse(['error' => 'User not authenticated'], 401);
        }

        $jsonInput = file_get_contents('php://input');
        $data = json_decode($jsonInput, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->jsonResponse(['error' => 'Invalid JSON provided'], 400);
        }

        try {
            $updatedUser = $this->authService->updateProfile($user, $data);
            return $this->jsonResponse(['success' => true, 'message' => 'Profile updated successfully.', 'user' => $updatedUser]);
        } catch (\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}