<?php

namespace App\Application\Services;

use App\Infrastructure\Repositories\UserRepository;
use App\Infrastructure\Models\User;
use Illuminate\Database\QueryException;

class UserRegistrationService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Register a new user (base account for all roles).
     * This creates the user but does NOT send verification email.
     * The verification email should be sent separately.
     */
    public function registerUser(array $data): User
    {
        if (empty($data['email']) || empty($data['password'])) {
            throw new \InvalidArgumentException("Email and password are required.");
        }

        if ($this->userRepository->findByEmail($data['email'])) {
            throw new \Exception("User with this email already exists.");
        }

        // Hash password
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);

        // Generate 4-digit verification code
        $verificationCode = random_int(1000, 9999);

		// Save user with is_verified = false
		try {
			$user = $this->userRepository->create([
				'name' => $data['name'] ?? null,
				'email' => $data['email'],
				'password' => $data['password'],
				'role' => $data['role'] ?? 'customer',
				'phone' => $data['phone'] ?? null,
				'is_verified' => false,
				'verification_code' => $verificationCode,
				// Require verification within 5 minutes
				'verification_code_expires_at' => date('Y-m-d H:i:s', strtotime('+5 minutes'))
			]);
		} catch (QueryException $e) {
			// Handle unique constraint violation on email gracefully
			$driverCode = $e->errorInfo[1] ?? null; // MySQL code (e.g., 1062)
			if ($e->getCode() === '23000' || $driverCode === 1062) {
				throw new \Exception('User with this email already exists.');
			}
			throw $e;
		}

        return $user;
    }
}