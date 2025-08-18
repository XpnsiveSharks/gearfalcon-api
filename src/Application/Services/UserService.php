<?php
declare(strict_types=1);

namespace App\Application\User;

use App\Domain\User\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\Entities\Profile;
use App\Domain\User\ValueObjects\ContactInfo;
use App\Domain\User\ValueObjects\Credentials;

final class UserService
{
    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    // Create a new user
    public function createUser(
        string $role,
        Profile $profile,
        ContactInfo $contactInfo,
        Credentials $credentials
    ): User {
        $id = uniqid('', true); // or use a proper UUID generator
        $user = new User($id, $role, $profile, $contactInfo, $credentials);

        $this->userRepository->save($user);

        return $user;
    }

    // Update profile
    public function updateProfile(string $userId, Profile $profile): void
    {
        $user = $this->userRepository->findById($userId);
        $user->updateProfile($profile);
        $this->userRepository->save($user);
    }

    // Update contact info
    public function updateContactInfo(string $userId, ContactInfo $contactInfo): void
    {
        $user = $this->userRepository->findById($userId);
        $user->updateContactInfo($contactInfo);
        $this->userRepository->save($user);
    }

    // Update credentials
    public function updateCredentials(string $userId, Credentials $credentials): void
    {
        $user = $this->userRepository->findById($userId);
        $user->updateCredentials($credentials);
        $this->userRepository->save($user);
    }

    // Activate/deactivate user
    public function activateUser(string $userId): void
    {
        $user = $this->userRepository->findById($userId);
        $user->activate();
        $this->userRepository->save($user);
    }

    public function deactivateUser(string $userId): void
    {
        $user = $this->userRepository->findById($userId);
        $user->deactivate();
        $this->userRepository->save($user);
    }
}
