<?php
declare(strict_types=1);

namespace App\Domain\User\Entities;

final class Profile
{
    private string $firstName;
    private string $lastName;
    private ?string $middleName;
    private ?string $avatarUrl;

    public function __construct(
        string $firstName,
        string $lastName,
        ?string $middleName = null,
        ?string $avatarUrl = null
    ) {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->middleName = $middleName;
        $this->avatarUrl = $avatarUrl;
    }

    // --- Getters ---
    public function getFirstName(): string { return $this->firstName; }
    public function getLastName(): string { return $this->lastName; }
    public function getMiddleName(): ?string { return $this->middleName; }
    public function getAvatarUrl(): ?string { return $this->avatarUrl; }

    // --- Immutable setters ---
    public function withFirstName(string $firstName): self {
        $clone = clone $this;
        $clone->firstName = $firstName;
        return $clone;
    }
    public function withLastName(string $lastName): self {
        $clone = clone $this;
        $clone->lastName = $lastName;
        return $clone;
    }
    public function withMiddleName(?string $middleName): self {
        $clone = clone $this;
        $clone->middleName = $middleName;
        return $clone;
    }
    public function withAvatarUrl(?string $avatarUrl): self {
        $clone = clone $this;
        $clone->avatarUrl = $avatarUrl;
        return $clone;
    }

    // --- Hydration ---
    public static function fromArray(array $data): self {
        return new self(
            $data['firstName'] ?? '', // support snake_case from DB
            $data['lastName'] ?? '',
            $data['middleName'] ?? null,
            $data['avatarUrl'] ?? null
        );
    }

    // --- Dehydration ---
    public function toArray(): array {
        return [
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'middleName' => $this->middleName,
            'avatarUrl' => $this->avatarUrl,
        ];
    }
}
