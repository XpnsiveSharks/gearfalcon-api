<?php

namespace App\Application\Services;

use App\Infrastructure\Repositories\UserRepository;
use App\Infrastructure\Models\User;
use App\Application\Exceptions\InvalidCredentialsException;

class AuthService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Authenticate a user by email + password.
     *
     * @throws InvalidCredentialsException
     */
    public function login(string $email, string $password): User
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !password_verify($password, $user->password)) {
            error_log("AuthService: Failed login attempt.");
            throw new InvalidCredentialsException();
        }

        if (password_needs_rehash($user->password, PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $this->userRepository->update($user->id, ['password,' => $newHash]);
        }

        return $user;
    }
    public function logout(): bool // TO DO
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        return true;
    }
}
