<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\Common\Entity;
use App\Domain\User\ValueObjects\Credentials;
use App\Domain\User\Entities\Profile;
use App\Domain\User\ValueObjects\ContactInfo;
use App\Domain\User\ValueObjects\Address;
//*********************************************************Aggregate Root*********************************************************//
final class User extends Entity
{
    private string $role;
    private Profile $profile;
    private ContactInfo $contactInfo;
    private Credentials $credentials;
    private Address $address;
    private bool $isActive;

    public function __construct(
        string $role,
        Profile $profile,
        ContactInfo $contactInfo,
        Credentials $credentials,
        Address $address
    ) {
        parent::__construct();
        $this->setRole($role);
        $this->profile = $profile;
        $this->contactInfo = $contactInfo;
        $this->credentials = $credentials;
        $this->address = $address;
        $this->isActive = true; // default active on creation
    }

    // Role management
    public function role(): string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $validRoles = ['Customer', 'Technician', 'Admin'];
        if (!in_array($role, $validRoles, true)) {
            throw new \InvalidArgumentException("Invalid role: $role");
        }
        $this->role = $role;
    }

    // Profile access
    public function profile(): Profile
    {
        return $this->profile;
    }

    public function updateProfile(Profile $profile): void
    {
        $this->profile = $profile;
        $this->touch();
    }

    // ContactInfo access
    public function contactInfo(): ContactInfo
    {
        return $this->contactInfo;
    }

    public function updateContactInfo(ContactInfo $contactInfo): void
    {
        $this->contactInfo = $contactInfo;
        $this->touch();
    }

    // Credentials access
    public function credentials(): Credentials
    {
        return $this->credentials;
    }

    public function updateCredentials(Credentials $credentials): void
    {
        $this->credentials = $credentials;
        $this->touch();
    }
    // Address Access
    public function address(): Address
    {
        return $this->address;
    }
    public function updateAddress(Address $address): void
    {
        $this->address = $address;
        $this->touch();
    }

    // Activation
    public function deactivate(): void
    {
        $this->isActive = false;
        $this->touch();
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }
}
