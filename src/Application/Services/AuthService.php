<?php

namespace App\Application\Services;

use App\Infrastructure\Repositories\UserRepository;
use App\Application\Services\EmailVerificationService;
use App\Infrastructure\Models\User;
use App\Application\Exceptions\InvalidCredentialsException;

class AuthService
{
    private UserRepository $userRepository;
    private EmailVerificationService $verificationService;

    public function __construct(
        UserRepository $userRepository,
        EmailVerificationService $verificationService
    )
    {
        $this->userRepository = $userRepository;
        $this->verificationService = $verificationService;
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

    /**
     * Changes a user's email, marks them as unverified, and sends a new verification code.
     *
     * @param User $user The user to update.
     * @param string $newEmail The new email address.
     * @return User The updated user object.
     * @throws \Exception If the new email is already in use.
     */
    public function changeEmailAndRequestVerification(User $user, string $newEmail): User
    {
        // 1. Check if the new email is already taken by another user.
        $existingUser = $this->userRepository->findByEmail($newEmail);
        if ($existingUser && $existingUser->id !== $user->id) {
            throw new \Exception('This email address is already in use by another account.');
        }

        // 2. Update the user's email and mark as unverified.
        $user->email = $newEmail;
        $user->is_verified = false;
        $user->email_verified_at = null;
        // The verification service will set the code and expiry.
        $user->save();

        // 3. Send a new verification code to the new email address.
        // This will also update the verification_code and expiry on the user model.
        $this->verificationService->sendVerificationCode($newEmail);

        // Return the updated user.
        return $user;
    }

    public function forgotPassword(string $email): void
    {
        $user = $this->userRepository->findByEmail($email);
        if ($user) {
            $this->verificationService->sendPasswordResetCode($email);
        }
    }

    public function verifyPasswordReset(string $email, string $code): bool
    {
        return $this->verificationService->verifyPasswordResetCode($email, $code);
    }

    public function resetPassword(string $email, string $code, string $password): void
    {
        if ($this->verifyPasswordReset($email, $code)) {
            $user = $this->userRepository->findByEmail($email);
            if ($user) {
                $this->userRepository->update($user->id, [
                    'password' => password_hash($password, PASSWORD_DEFAULT),
                    'password_reset_code' => null,
                    'password_reset_code_expires_at' => null,
                    'password_reset_verified' => 0
                ]);
            }
        }
    }

    public function updateProfile(User $user, array $data): User
    {
        $updateData = [];
        if (isset($data['name'])) {
            $updateData['name'] = $data['name'];
        }
        if (isset($data['phone'])) {
            $updateData['phone'] = $data['phone'];
        }

        if (!empty($updateData)) {
            $this->userRepository->update($user->id, $updateData);
            $user->refresh();
        }

        return $user;
    }
}
