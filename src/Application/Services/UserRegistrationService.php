<?php
namespace App\Application\Services;

use App\Infrastructure\Repositories\UserRepository;
use App\Infrastructure\Models\User;
// all user must use this service when creating new account
class UserRegistrationService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Register a new user (base account for all roles).
     */
    public function registerUser(array $data): User
    {
        // Basic validation 
        if (empty($data['email']) || empty($data['password'])) {
            throw new \InvalidArgumentException("Email and password are required.");
        }

        if ($this->userRepository->findByEmail($data['email'])) {
            throw new \Exception("User with this email already exists.");
        }

        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        return $this->userRepository->create([
            'name' => $data['name'] ?? null,
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => $data['role'] ?? 'customer',
            'phone' => $data['phone'] ?? null,
        ]);
    }
}
