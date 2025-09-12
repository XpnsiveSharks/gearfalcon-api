<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\Common\Entity;
use App\Domain\User\Entities\Profile;
use App\Domain\User\ValueObjects\Address;
use App\Domain\User\ValueObjects\ContactInfo;
use App\Domain\User\ValueObjects\Credentials;

//*********************************************************Aggregate Root*********************************************************//
final class User extends Entity
{
    private string $role;
    private ?Profile $profile;
    private ?ContactInfo $contactInfo;
    private Credentials $credentials;
    private ?Address $address;
    private bool $isActive;

    public function __construct(
        string $role,
        ?Profile $profile,
        ?ContactInfo $contactInfo,
        Credentials $credentials,
        ?Address $address
    ) {
        parent::__construct();
        $this->setRole($role);
        $this->profile = $profile;
        $this->contactInfo = $contactInfo;
        $this->credentials = $credentials;
        $this->address = $address;
        $this->isActive = true;
    }


    public function getRole(): string
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

    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function updateProfile(Profile $profile): void
    {
        $this->profile = $profile;
        $this->touch();
    }

    public function getContactInfo(): ?ContactInfo
    {
        return $this->contactInfo;
    }

    public function updateContactInfo(ContactInfo $contactInfo): void
    {
        $this->contactInfo = $contactInfo;
        $this->touch();
    }

    public function getCredentials(): Credentials
    {
        return $this->credentials;
    }

    public function updateCredentials(Credentials $credentials): void
    {
        $this->credentials = $credentials;
        $this->touch();
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function updateAddress(Address $address): void
    {
        $this->address = $address;
        $this->touch();
    }

    public function deactivate(): void
    {
        $this->isActive = false;
        $this->touch();
    }

    public function activate(): void
    {
        $this->isActive = true;
        $this->touch();
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    // -----------------------------
    // HYDRATION
    // -----------------------------
    public static function fromArray(array $data): self
    {
        // --- Hydrate nested value objects safely ---
        $profile = Profile::fromArray($data['profile'] ?? []);
        $contactInfo = ContactInfo::fromArray($data['contactInfo'] ?? []);

        $credentialsData = $data['credentials'] ?? [];
        if (isset($credentialsData['password_hash'])) {
            $credentials = Credentials::fromHashed(
                $credentialsData['email'] ?? throw new \InvalidArgumentException('Email is required'),
                $credentialsData['password_hash']
            );
        } else {
            $credentials = new Credentials(
                $credentialsData['email'] ?? throw new \InvalidArgumentException('Email is required'),
                $credentialsData['password'] ?? throw new \InvalidArgumentException('Password is required')
            );
        }

        $address = Address::fromArray($data['address'] ?? []);

        // --- Create the User aggregate ---
        $user = new self(
            $data['role'] ?? 'Customer',
            $profile,
            $contactInfo,
            $credentials,
            $address
        );

        // Optional fields
        if (isset($data['id'])) {
            $user->setId($data['id']);
        }

        if (isset($data['is_active'])) {
            $data['is_active'] ? $user->activate() : $user->deactivate();
        }

        if (isset($data['created_at'])) {
            $user->setCreatedAt(new \DateTimeImmutable($data['created_at']));
        }

        if (isset($data['updated_at'])) {
            $user->setUpdatedAt(new \DateTimeImmutable($data['updated_at']));
        }

        if (isset($data['deleted_at'])) {
            $user->setDeletedAt($data['deleted_at'] ? new \DateTimeImmutable($data['deleted_at']) : null);
        }

        return $user;
    }



    // -----------------------------
    // DEHYDRATION
    // -----------------------------
    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'role' => $this->getRole(),
            'is_active' => $this->isActive(),
            'first_name' => $this->profile?->getFirstName(),
            'last_name' => $this->profile?->getLastName(),
            'middle_name' => $this->profile?->getMiddleName(),
            'avatar_url' => $this->profile?->getAvatarUrl(),
            'phone' => $this->contactInfo?->getPhone(),
            'email' => $this->credentials->getEmail(),
            'password_hash' => $this->credentials->getPasswordHash(),
            'house_number' => $this->address?->getHouseNumber(),
            'street' => $this->address?->getStreet(),
            'barangay' => $this->address?->getBarangay(),
            'city' => $this->address?->getCity(),
            'province' => $this->address?->getProvince(),
            'region' => $this->address?->getRegion(),
            'postal_code' => $this->address?->getPostalCode(),
            'created_at' => $this->getCreatedAtUtc()->format('Y-m-d H:i:s'),
            'updated_at' => $this->getLastModifiedAtUtc()->format('Y-m-d H:i:s'),
            'deleted_at' => property_exists($this, 'deletedAtUtc') && $this->deletedAtUtc
                ? $this->deletedAtUtc->format('Y-m-d H:i:s')
                : null,
        ];
    }
}
