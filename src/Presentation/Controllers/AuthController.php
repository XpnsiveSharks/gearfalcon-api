<?php
namespace App\Presentation\Controllers;

use App\Application\Services\AuthService;
use App\Application\Services\UserRegistrationService;
use App\Application\Services\EmailVerificationService;
use App\Application\Exceptions\InvalidCredentialsException;
use App\Infrastructure\Models\User;
use Firebase\JWT\JWT;

class AuthController
{
    private AuthService $authService;
    private UserRegistrationService $userRegistrationService;
    private EmailVerificationService $verificationService;

    public function __construct(
        AuthService $authService,
        UserRegistrationService $userRegistrationService,
        EmailVerificationService $verificationService
    ) {
        $this->authService = $authService;
        $this->userRegistrationService = $userRegistrationService;
        $this->verificationService = $verificationService;
    }

    private function jsonResponse(array $data, int $statusCode = 200): string
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        return json_encode($data);
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

			// Issue JWT token (verification/guarding is handled by middleware)
			$issuedAt = time();
			$expiresAt = $issuedAt + (int)($_ENV['JWT_TTL_SECONDS'] ?? 3600);
			$payload = [
				'sub' => $user->id,
				'name' => $user->name,
				'email' => $user->email,
				'role' => $user->role,
				'is_verified' => (bool)$user->is_verified,
				'iat' => $issuedAt,
				'exp' => $expiresAt,
			];

			$secret = $_ENV['JWT_SECRET'] ?? '';
			$token = JWT::encode($payload, $secret, 'HS256');

			return $this->jsonResponse([
				'success' => true,
				'token' => $token,
				'expires_in' => $expiresAt - $issuedAt,
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
                    'role' => $user->role,
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
        $code  = $request['code'] ?? '';

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
}