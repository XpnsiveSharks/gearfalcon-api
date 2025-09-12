<?php
namespace App\Presentation\Controllers;

use App\Application\Services\AuthService;
use App\Application\Services\UserRegistrationService;
use App\Application\Exceptions\InvalidCredentialsException;
use App\Infrastructure\Models\User;

class AuthController
{
    private AuthService $authService;
    private UserRegistrationService $userRegistrationService;

    public function __construct(AuthService $authService, UserRegistrationService $userRegistrationService)
    {
        $this->authService = $authService;
        $this->userRegistrationService = $userRegistrationService;
    }

    private function jsonResponse(array $data, int $statusCode = 200): string
    {
        http_response_code($statusCode);
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

            return $this->jsonResponse([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ]
            ]);
        } catch (InvalidCredentialsException $e) {
            return $this->jsonResponse(['error' => $e->getMessage()], 401);
        }
    }

    public function register(array $request): string
    {
        try {
            $user = $this->userRegistrationService->registerUser($request);

            return $this->jsonResponse([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ]
            ], 201);
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
