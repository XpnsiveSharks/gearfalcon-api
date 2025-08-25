<?php
declare(strict_types=1);

namespace App\Application\User;

use App\Domain\User\User;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Domain\User\Entities\Profile;
use App\Domain\User\ValueObjects\ContactInfo;
use App\Domain\User\ValueObjects\Credentials;
use App\Domain\User\ValueObjects\Address;

final class UserService
{
    private UserRepositoryInterface $users;

    public function __construct(UserRepositoryInterface $users)
    {
        $this->users = $users;
    }

    public function registerUser(
        string $role,
        Profile $profile,
        ContactInfo $contactInfo,
        Credentials $credentials,
        Address $address
    ): User {
        $user = new User(
            $role,
            $profile,
            $contactInfo,
            $credentials,
            $address
        );

        $this->users->save($user);

        return $user;
    }
}
